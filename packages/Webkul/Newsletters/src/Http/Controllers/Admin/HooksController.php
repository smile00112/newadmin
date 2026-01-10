<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Repositories\CustomerNumberRepository;
use Webkul\Newsletters\Repositories\MailingListRepository;
use Webkul\Newsletters\Repositories\StopListRepository;
use Webkul\Newsletters\Events\MailingListStatsUpdated;
use Webkul\Newsletters\Repositories\VacapInstanceRepository;
use Webkul\Newsletters\Events\CustomerNumberIncomingMessage;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Services\GreenAPIService;

class HooksController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected StopListRepository $stopListRepository,
        protected CustomerNumberRepository $customerNumberRepository,
        protected MailingListRepository $mailingListRepository

    ) {}

    /**
     * Handle webhook from GreenAPI and broadcast stats updates.
     */
    public function get_hook(Request $request)
    {
//        Log::info("HOOK__GreenAPI hook received:", [
//            'body' => $request->all(),
//        ]);

        try {
            // Process the webhook data
            $webhookData = $request->all();
            if(!empty($webhookData['body'])) $webhookData = $webhookData['body']; ////?????

            // Extract relevant information from webhook
            $chatId = !empty($webhookData['chatId']) ?? null; //"79206003788@c.us"
            $messageType = !empty($webhookData['typeWebhook']) ? $webhookData['typeWebhook'] : null;
            $messageData = !empty($webhookData['messageData']) ? $webhookData['messageData'] : null;
            $idMessage = !empty($webhookData['idMessage']) ? $webhookData['idMessage'] : null; //id сообщения
            $status = !empty($webhookData['status']) ? $webhookData['status'] : null;
            $senderData = !empty($webhookData['senderData']) ? $webhookData['senderData'] : null; //
            $instanceData = !empty($webhookData['instanceData']) ? $webhookData['instanceData'] : null; //

            // определение типа хуков
            switch ($messageType) {
                case 'incomingMessageReceived': //входящее сообщение
//                    Log::info("HOOK__GreenAPI hook TYPE:", [
//                        'body' => '//входящее сообщение',
//                    ]);
                    if (!empty($senderData) && !empty($senderData['sender']) && !empty($instanceData)) {
                        $this->handleIncomingMessage($senderData['sender'], $instanceData, $messageData);
                    }
                    break;
                case 'outgoingMessageStatus':   //статус отправленного сообщения
//                    Log::info("HOOK__GreenAPI hook TYPE:", [
//                        'body' => '//статус отправленного сообщения',
//                    ]);
                    $this->handleOutgoingMessageStatus($idMessage, $status);
                    break;
                case 'stateInstanceChanged':    //изменение состояния инстанса
                    $stateInstance = !empty($webhookData['stateInstance']) ? $webhookData['stateInstance'] : null;
                    $this->handleStateInstanceChanged($instanceData, $stateInstance);
                    break;
                default:
//                    Log::info("HOOK__Unhandled webhook type: {$messageType}");
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
//            Log::error("HOOK__Error processing webhook:", [
//                'error' => $e->getMessage(),
//                'trace' => $e->getTraceAsString()
//            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle incoming message webhook.
     */
    private function handleIncomingMessage(string $chatId, array $instanceData, ?array $messageData = null): void
    {
        // Extract phone number from chatId (remove @c.us suffix)
        $phoneNumber = str_replace('@c.us', '', $chatId);

        $idInstance = $instanceData['idInstance'] ?? null;
        $customerNumber = null;

        if($idInstance){
            $instance = VacapInstance::with('customerNumbers')
                ->whereHas('customerNumbers', function($q) use($phoneNumber){
                    $q->where('phone_number', $phoneNumber);
            })->first();

            if($instance){
                $customerNumber = $instance->customerNumbers->count() ? $instance->customerNumbers->first() : null;

                //добавляем прикперлённый номер телефона к инстансу
                $instancePhoneNumber = str_replace('@c.us', '', $instanceData['wid'] ?? '');
                if($instancePhoneNumber){
                    $instance->update([
                        'phone' => $instancePhoneNumber
                    ]);
                }
            }
        }

        if(empty($customerNumber)){
//            Log::warning("HOOK__handleIncomingMessage  customerNumber NOT found", [
//                'chatId' => $chatId,
//                'instanceData' => $instanceData,
//            ]);

            return;
        }

        if ($customerNumber) {
            // Update incoming_message status
            DB::table('newsletters_customer_numbers')
                ->where('id', $customerNumber->id)
                ->update([
                    'incoming_message' => true,
                    'updated_at' => now()
                ]);

            // Broadcast stats update
            $this->broadcastStatsUpdate($customerNumber->mailing_list_id);

            // Broadcast customer number incoming message update
            try {
                // Ensure the model reflects latest state before broadcasting
                $customerNumber->incoming_message = true;
                broadcast(new CustomerNumberIncomingMessage($customerNumber));
            } catch (\Exception $e) {
                Log::error('HOOK__Failed to broadcast CustomerNumber incoming message', [
                    'customer_number_id' => $customerNumber->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Process auto-reply if enabled
            if ($messageData) {
                $this->processAutoReply($customerNumber, $messageData);
            }

//            Log::info("HOOK__Incoming message processed", [
//                'phone_number' => $phoneNumber,
//                'mailing_list_id' => $customerNumber->mailing_list_id
//            ]);
        }
    }

    /**
     * Handle outgoing message status webhook.
     */
    private function handleOutgoingMessageStatus(string $chatId, string $status): void
    {
        $phoneNumber = str_replace('@c.us', '', $chatId);

        $customerNumber = DB::table('newsletters_customer_numbers')
            ->where('greenapi_chat_id', $phoneNumber)
            ->first();

//        Log::info("HOOK__handleOutgoingMessageStatus", [
//            'chatId' => $chatId,
//            'phone_number' => $phoneNumber,
//            '$customerNumber' => $customerNumber,
//        ]);


        if ($customerNumber) {
            switch ($status):
                case 'read':            //сообщение просмотрено
                    // Update sent status
                    DB::table('newsletters_customer_numbers')
                        ->where('id', $customerNumber->id)
                        ->update([
                            'viewed' => true,
                            'updated_at' => now()
                        ]);
                    break;
                case 'delivered':       //сообщение доставлено
                    // Update delivered status
                    DB::table('newsletters_customer_numbers')
                        ->where('id', $customerNumber->id)
                        ->update([
                            'delivered' => true,
                            'updated_at' => now()
                        ]);
                    break;
                default:
                    break;
            endswitch;

            // Broadcast stats update
            $this->broadcastStatsUpdate($customerNumber->mailing_list_id);

//            Log::info("HOOK__Message delivery status updated", [
//                'phone_number' => $phoneNumber,
//                'mailing_list_id' => $customerNumber->mailing_list_id,
//                'status' => $status,
//            ]);
        }

//        Log::error("HOOK__Message not found", [
//            'phone_number' => $phoneNumber,
//            'customerNumber' => $customerNumber,
//            'status' => $status,
//        ]);
    }

    /**
     * Handle message delivered webhook.
     */
    private function handleMessageDelivered(string $chatId, array $messageData): void
    {
        $phoneNumber = str_replace('@c.us', '', $chatId);

        $customerNumber = DB::table('newsletters_customer_numbers')
            ->where('phone_number', $phoneNumber)
            ->first();

        if ($customerNumber) {
            DB::table('newsletters_customer_numbers')
                ->where('id', $customerNumber->id)
                ->update([
                    'delivered' => true,
                    'updated_at' => now()
                ]);

            $this->broadcastStatsUpdate($customerNumber->mailing_list_id);
        }
    }

    /**
     * Handle message read webhook.
     */
    private function handleMessageRead(string $chatId, array $messageData): void
    {
        $phoneNumber = str_replace('@c.us', '', $chatId);

        $customerNumber = DB::table('newsletters_customer_numbers')
            ->where('phone_number', $phoneNumber)
            ->first();

        if ($customerNumber) {
            DB::table('newsletters_customer_numbers')
                ->where('id', $customerNumber->id)
                ->update([
                    'viewed' => true,
                    'updated_at' => now()
                ]);

            $this->broadcastStatsUpdate($customerNumber->mailing_list_id);
        }
    }

    /**
     * Test route to broadcast stats update by MailingList id.
     */
    public function testBroadcastStatsUpdate(int $id): JsonResponse
    {
        try {
            // Verify MailingList exists
            $mailingList = $this->mailingListRepository->find($id);

            if (!$mailingList) {
                return response()->json([
                    'status' => 'error',
                    'message' => "MailingList with id {$id} not found"
                ], 404);
            }

            // Call broadcastStatsUpdate
            $this->broadcastStatsUpdate($id);

//            Log::info("HOOK__Test broadcast stats update called", [
//                'mailing_list_id' => $id
//            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Stats update broadcasted successfully',
                'mailing_list_id' => $id
            ]);

        } catch (\Exception $e) {
            Log::error("HOOK__Error in testBroadcastStatsUpdate:", [
                'mailing_list_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Handle state instance changed webhook.
     * Updates the active status of the instance based on its state.
     */
    private function handleStateInstanceChanged(?array $instanceData, ?string $stateInstance): void
    {
        if (empty($instanceData) || empty($instanceData['idInstance'])) {
            Log::warning("HOOK__handleStateInstanceChanged: Missing instanceData or idInstance", [
                'instanceData' => $instanceData,
                'stateInstance' => $stateInstance,
            ]);
            return;
        }

        $idInstance = $instanceData['idInstance'];

        // Find instance by phone (wid) or login
        // Try to find by phone first (wid from instanceData)
        $instance = null;

        if (!empty($instanceData['wid'])) {
            $instancePhoneNumber = str_replace('@c.us', '', $instanceData['wid']);
            $instance = VacapInstance::where('phone', $instancePhoneNumber)->first();
        }

        // If not found by phone, try to find by login (idInstance might match login)
        if (!$instance && !empty($idInstance)) {
            $instance = VacapInstance::where('login', (string)$idInstance)->first();
        }

        if (!$instance) {
            Log::warning("HOOK__handleStateInstanceChanged: Instance not found", [
                'idInstance' => $idInstance,
                'instanceData' => $instanceData,
                'stateInstance' => $stateInstance,
            ]);
            return;
        }

        // Determine active status based on stateInstance
        // active = true only when stateInstance === 'authorized'
        $active = ($stateInstance === 'authorized');

        $instance->update([
            'active' => $active,
        ]);

        Log::info("HOOK__State instance changed", [
            'instance_id' => $instance->id,
            'idInstance' => $idInstance,
            'stateInstance' => $stateInstance,
            'active' => $active,
        ]);
    }

    /**
     * Process auto-reply for incoming message.
     */
    private function processAutoReply(CustomerNumber $customerNumber, array $messageData): void
    {
        try {
            // Get mailing list
            $mailingList = $customerNumber->mailingList;
            
            if (!$mailingList) {
                Log::warning('HOOK__processAutoReply: Mailing list not found', [
                    'customer_number_id' => $customerNumber->id,
                ]);
                return;
            }

            // Check if auto-reply is enabled and channel is WhatsApp
            if ($mailingList->channel_type !== 'whatsapp' || !$mailingList->auto_reply_enabled) {
                return;
            }

            // Check if auto-replies are configured
            $autoReplies = $mailingList->auto_replies;
            if (empty($autoReplies) || !is_array($autoReplies)) {
                return;
            }

            // Extract message text from messageData
            $messageText = $this->extractMessageText($messageData);
            if (empty($messageText)) {
                Log::info('HOOK__processAutoReply: No text message found in messageData', [
                    'customer_number_id' => $customerNumber->id,
                    'message_data' => $messageData,
                ]);
                return;
            }

            // Convert message text to lowercase for case-insensitive matching
            $messageTextLower = mb_strtolower($messageText, 'UTF-8');

            // Search for matching phrase
            foreach ($autoReplies as $autoReply) {
                if (empty($autoReply['phrase']) || empty($autoReply['response'])) {
                    continue;
                }

                // Case-insensitive substring search
                $phraseLower = mb_strtolower($autoReply['phrase'], 'UTF-8');
                if (mb_strpos($messageTextLower, $phraseLower) !== false) {
                    // Found a match, send auto-reply
                    $this->sendAutoReply($customerNumber, $autoReply['response']);
                    return; // Send only first match
                }
            }

        } catch (\Exception $e) {
            Log::error('HOOK__processAutoReply: Error processing auto-reply', [
                'customer_number_id' => $customerNumber->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Extract message text from messageData.
     */
    private function extractMessageText(array $messageData): ?string
    {
        // Try to get text from textMessage or extendedTextMessage
        if (!empty($messageData['textMessage'])) {
            return $messageData['textMessage'];
        }

        if (!empty($messageData['extendedTextMessage']['text'])) {
            return $messageData['extendedTextMessage']['text'];
        }

        // Check for typeMessage field
        $typeMessage = $messageData['typeMessage'] ?? null;
        if ($typeMessage === 'textMessage' && !empty($messageData['textMessage'])) {
            return $messageData['textMessage'];
        }

        if ($typeMessage === 'extendedTextMessage' && !empty($messageData['extendedTextMessage']['text'])) {
            return $messageData['extendedTextMessage']['text'];
        }

        return null;
    }

    /**
     * Send auto-reply message.
     */
    private function sendAutoReply(CustomerNumber $customerNumber, string $response): void
    {
        try {
            // Get WhatsApp instance
            $instance = $customerNumber->whatsAppInstance;
            if (!$instance) {
                Log::warning('HOOK__sendAutoReply: WhatsApp instance not found', [
                    'customer_number_id' => $customerNumber->id,
                ]);
                return;
            }

            // Get phone number
            $phoneNumber = $customerNumber->phone_number;
            if (empty($phoneNumber)) {
                Log::warning('HOOK__sendAutoReply: Phone number not found', [
                    'customer_number_id' => $customerNumber->id,
                ]);
                return;
            }

            // Format phone number (ensure it's in correct format)
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            $chatId = $phoneNumber . '@c.us';

            // Create GreenAPI service and send message
            $greenApiService = new GreenAPIService($instance->link_name, $instance->login, $instance->password);
            $result = $greenApiService->sendMessage($chatId, $response);

            Log::info('HOOK__sendAutoReply: Auto-reply sent successfully', [
                'customer_number_id' => $customerNumber->id,
                'phone_number' => $phoneNumber,
                'response_id' => $result['idMessage'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('HOOK__sendAutoReply: Error sending auto-reply', [
                'customer_number_id' => $customerNumber->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Broadcast stats update for a mailing list.
     */
    private function broadcastStatsUpdate(int $mailingListId): void
    {
        try {
            // Get updated stats from database
            $mailingList = $this->mailingListRepository->with('customerNumbers')->withCount([
                'customerNumbers as numbers_delivered' => function ($query) {
                    $query->where('sending', true)->orWhere('send_error', true);
                },
                'customerNumbers as numbers_viewed' => function ($query) {
                    $query->where('viewed', true);
                },
                'customerNumbers as incoming_messages_count' => function ($query) {
                    $query->where('incoming_message', true);
                }
            ])->find($mailingListId);

//            $stats = DB::table('newsletters_customer_numbers')
//                ->where('mailing_list_id', $mailingListId)
//                ->selectRaw('
//                    COUNT(*) as total_count,
//                    SUM(CASE WHEN delivered = 1 THEN 1 ELSE 0 END) as sent_count,
//                    SUM(CASE WHEN incoming_message = 1 THEN 1 ELSE 0 END) as incoming_count,
//                    SUM(CASE WHEN viewed = 1 THEN 1 ELSE 0 END) as viewed_count
//                ')
//                ->first();

            if ($mailingList) {
                $stats = [
                    'sent_count' => (int) $mailingList->numbers_delivered,
                    'incoming_count' => (int) $mailingList->incoming_messages_count,
                    'viewed_count' => (int) $mailingList->numbers_viewed,
                    'total_count' => (int) $mailingList->customerNumbers->count()
                ];

//                Log::info("HOOK__DATA to broacast", [
//                    'sent_count' => (int) $mailingList->numbers_delivered,
//                    'incoming_count' => (int) $mailingList->incoming_messages_count,
//                    'viewed_count' => (int) $mailingList->numbers_viewed,
//                    'total_count' => (int) $mailingList->customerNumbers->count(),
//                    '$mailingListId' => $mailingListId
//                ]);

                // Broadcast the update
                broadcast(new MailingListStatsUpdated($mailingListId, $stats));

                Log::info('HOOK__Mailing list stats broadcasted from webhook', [
                    'mailing_list_id' => $mailingListId,
                    'stats' => $stats
                ]);
            }
        } catch (\Exception $e) {
            Log::error('HOOK__Failed to broadcast mailing list stats from webhook', [
                'mailing_list_id' => $mailingListId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
