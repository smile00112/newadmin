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
        Log::info("HOOK__GreenAPI hook received:", [
            'body' => $request->all(),
        ]);

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
                    Log::info("HOOK__GreenAPI hook TYPE:", [
                        'body' => '//входящее сообщение',
                    ]);
                    $this->handleIncomingMessage($senderData['sender'], $instanceData);
                    break;
                case 'outgoingMessageStatus':   //статус отправленного сообщения
                    Log::info("HOOK__GreenAPI hook TYPE:", [
                        'body' => '//статус отправленного сообщения',
                    ]);
                    $this->handleOutgoingMessageStatus($idMessage, $status);
                    break;
                default:
                    Log::info("HOOK__Unhandled webhook type: {$messageType}");
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error("HOOK__Error processing webhook:", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle incoming message webhook.
     */
    private function handleIncomingMessage(string $chatId, array $instanceData): void
    {

        // Extract phone number from chatId (remove @c.us suffix)
        $phoneNumber = str_replace('@c.us', '', $chatId);

        $idInstance = $instanceData['idInstance'];
        if($idInstance){
            $instance = VacapInstance::with('customerNumbers')
                //->where('login', $phoneNumber)
                ->whereHas('customerNumbers', function($q) use($phoneNumber){
                    $q->where('phone_number', $phoneNumber);
            })->first();

            if($instance){
                $customerNumber = $instance->customerNumbers->count() ? $instance->customerNumbers->first() : null;

                //добавляем прикперлённый номер телефона к инстансу
                $instancePhoneNumber = str_replace('@c.us', '', $instanceData['wid']);
                if($instancePhoneNumber){
                    $instance->update([
                        'phone' => $instancePhoneNumber
                    ]);
                }
            }
        }

        if(empty($customerNumber)){
            Log::warning("HOOK__handleIncomingMessage  customerNumber NOT found", [
                'chatId' => $chatId,
                'instanceData' => $instanceData,
            ]);

            return;
        }

        // Find customer number by phone
//        $customerNumber = DB::table('newsletters_customer_numbers')
//            ->where('phone_number', $phoneNumber)
//            ->orderBy('id', 'desc')
//            ->first();


        Log::info("HOOK__handleIncomingMessage", [
            'chatId' => $chatId,
            'phone_number' => $phoneNumber,
            '$customerNumber' => $customerNumber,
        ]);


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

            Log::info("HOOK__Incoming message processed", [
                'phone_number' => $phoneNumber,
                'mailing_list_id' => $customerNumber->mailing_list_id
            ]);
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

        Log::info("HOOK__handleOutgoingMessageStatus", [
            'chatId' => $chatId,
            'phone_number' => $phoneNumber,
            '$customerNumber' => $customerNumber,
        ]);


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

            Log::info("HOOK__Message delivery status updated", [
                'phone_number' => $phoneNumber,
                'mailing_list_id' => $customerNumber->mailing_list_id,
                'status' => $status,
            ]);
        }

        Log::error("HOOK__Message not found", [
            'phone_number' => $phoneNumber,
            'customerNumber' => $customerNumber,
            'status' => $status,
        ]);
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

            Log::info("HOOK__Test broadcast stats update called", [
                'mailing_list_id' => $id
            ]);

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

                Log::info("HOOK__DATA to broacast", [
                    'sent_count' => (int) $mailingList->numbers_delivered,
                    'incoming_count' => (int) $mailingList->incoming_messages_count,
                    'viewed_count' => (int) $mailingList->numbers_viewed,
                    'total_count' => (int) $mailingList->customerNumbers->count(),
                    '$mailingListId' => $mailingListId
                ]);

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
