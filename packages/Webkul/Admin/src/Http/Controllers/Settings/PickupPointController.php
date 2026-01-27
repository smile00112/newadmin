<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\PickupPointRequest;
use Webkul\Inventory\Repositories\PickupPointRepository;

class PickupPointController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected PickupPointRepository $pickupPointRepository) {}

    /**
     * Get pickup points for inventory source.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $id): JsonResponse
    {
        $pickupPoints = $this->pickupPointRepository->findWhere(['inventory_source_id' => $id]);

        $pickupPoints = $pickupPoints->map(function ($point) {
            return [
                'id'                => $point->id,
                'name'              => $point->name,
                'latitude'          => $point->latitude,
                'longitude'         => $point->longitude,
                'address'           => $point->address,
                'working_hours'    => $point->working_hours,
                'map_icon'          => $point->map_icon ? Storage::url($point->map_icon) : null,
                'inventory_source_id' => $point->inventory_source_id,
            ];
        });

        return new JsonResponse([
            'data' => $pickupPoints,
        ]);
    }

    /**
     * Store a newly created pickup point.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PickupPointRequest $request): JsonResponse
    {
        Event::dispatch('inventory.pickup_point.create.before');

        $data = $request->only([
            'name',
            'latitude',
            'longitude',
            'address',
            'working_hours',
            'inventory_source_id',
        ]);

        // Handle file upload
        if ($request->hasFile('map_icon')) {
            $data['map_icon'] = $request->file('map_icon')->store('pickup_points');
        }

        $pickupPoint = $this->pickupPointRepository->create($data);

        Event::dispatch('inventory.pickup_point.create.after', $pickupPoint);

        return new JsonResponse([
            'message' => trans('admin::app.settings.pickup-points.create-success'),
            'data'    => [
                'id'                => $pickupPoint->id,
                'name'              => $pickupPoint->name,
                'latitude'          => $pickupPoint->latitude,
                'longitude'         => $pickupPoint->longitude,
                'address'           => $pickupPoint->address,
                'working_hours'    => $pickupPoint->working_hours,
                'map_icon'          => $pickupPoint->map_icon ? Storage::url($pickupPoint->map_icon) : null,
                'inventory_source_id' => $pickupPoint->inventory_source_id,
            ],
        ]);
    }

    /**
     * Update the specified pickup point.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PickupPointRequest $request, int $id): JsonResponse
    {
        $pickupPoint = $this->pickupPointRepository->findOrFail($id);

        Event::dispatch('inventory.pickup_point.update.before', $id);

        $data = $request->only([
            'name',
            'latitude',
            'longitude',
            'address',
            'working_hours',
            'inventory_source_id',
        ]);

        // Handle file upload
        if ($request->hasFile('map_icon')) {
            // Delete old file if exists
            if ($pickupPoint->map_icon) {
                Storage::delete($pickupPoint->map_icon);
            }

            $data['map_icon'] = $request->file('map_icon')->store('pickup_points');
        }

        $pickupPoint = $this->pickupPointRepository->update($data, $id);

        Event::dispatch('inventory.pickup_point.update.after', $pickupPoint);

        return new JsonResponse([
            'message' => trans('admin::app.settings.pickup-points.update-success'),
            'data'    => [
                'id'                => $pickupPoint->id,
                'name'              => $pickupPoint->name,
                'latitude'          => $pickupPoint->latitude,
                'longitude'         => $pickupPoint->longitude,
                'address'           => $pickupPoint->address,
                'working_hours'    => $pickupPoint->working_hours,
                'map_icon'          => $pickupPoint->map_icon ? Storage::url($pickupPoint->map_icon) : null,
                'inventory_source_id' => $pickupPoint->inventory_source_id,
            ],
        ]);
    }

    /**
     * Remove the specified pickup point.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $pickupPoint = $this->pickupPointRepository->findOrFail($id);

        try {
            Event::dispatch('inventory.pickup_point.delete.before', $id);

            // Delete file if exists
            if ($pickupPoint->map_icon) {
                Storage::delete($pickupPoint->map_icon);
            }

            $this->pickupPointRepository->delete($id);

            Event::dispatch('inventory.pickup_point.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.pickup-points.delete-success'),
            ]);
        } catch (\Exception $e) {
            report($e);

            return new JsonResponse([
                'message' => trans('admin::app.settings.pickup-points.delete-failed'),
            ], 500);
        }
    }
}
