<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\WorkOrderService;
use App\Repositories\WorkOrderRepository;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Asset;
use App\Events\WorkOrderCreated;
use App\Events\WorkOrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;

class WorkOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkOrderService $workOrderService;
    private $workOrderRepository;
    private User $user;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the repository
        $this->workOrderRepository = Mockery::mock(WorkOrderRepository::class);
        
        // Create the service with mocked repository
        $this->workOrderService = new WorkOrderService($this->workOrderRepository);

        // Create test data
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create();

        // Fake events
        Event::fake([
            WorkOrderCreated::class,
            WorkOrderStatusChanged::class
        ]);
    }

    /** @test */
    public function it_creates_work_order_and_dispatches_event()
    {
        $data = [
            'code' => 'WO001',
            'title' => 'Test Work Order',
            'description' => 'Test Description',
            'type' => 'corrective',
            'priority' => 'high',
            'status' => 'open',
            'assigned_to' => $this->user->id,
            'asset_id' => $this->asset->id,
            'due_date' => now()->addDays(7)->toDateString()
        ];

        $workOrder = WorkOrder::factory()->make($data);

        $this->workOrderRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($workOrder);

        $result = $this->workOrderService->createWorkOrder($data);

        $this->assertEquals($workOrder, $result);
        Event::assertDispatched(WorkOrderCreated::class);
    }

    /** @test */
    public function it_updates_work_order_status_and_dispatches_event()
    {
        $workOrder = WorkOrder::factory()->create([
            'status' => 'open'
        ]);

        $this->workOrderRepository
            ->shouldReceive('update')
            ->once()
            ->with($workOrder->id, ['status' => 'in_progress'])
            ->andReturn($workOrder);

        $result = $this->workOrderService->updateStatus($workOrder->id, 'in_progress');

        $this->assertEquals($workOrder, $result);
        Event::assertDispatched(WorkOrderStatusChanged::class);
    }

    /** @test */
    public function it_assigns_work_order_to_user()
    {
        $workOrder = WorkOrder::factory()->create();
        $newUser = User::factory()->create();

        $this->workOrderRepository
            ->shouldReceive('update')
            ->once()
            ->with($workOrder->id, ['assigned_to' => $newUser->id])
            ->andReturn($workOrder);

        $result = $this->workOrderService->assignToUser($workOrder->id, $newUser->id);

        $this->assertEquals($workOrder, $result);
    }

    /** @test */
    public function it_updates_work_order_progress()
    {
        $workOrder = WorkOrder::factory()->create([
            'progress' => 0
        ]);

        $this->workOrderRepository
            ->shouldReceive('update')
            ->once()
            ->with($workOrder->id, ['progress' => 50])
            ->andReturn($workOrder);

        $result = $this->workOrderService->updateProgress($workOrder->id, 50);

        $this->assertEquals($workOrder, $result);
    }

    /** @test */
    public function it_completes_work_order()
    {
        $workOrder = WorkOrder::factory()->create([
            'status' => 'in_progress',
            'progress' => 75
        ]);

        $this->workOrderRepository
            ->shouldReceive('update')
            ->once()
            ->with($workOrder->id, [
                'status' => 'completed',
                'progress' => 100,
                'completed_at' => Mockery::any()
            ])
            ->andReturn($workOrder);

        $result = $this->workOrderService->completeWorkOrder($workOrder->id);

        $this->assertEquals($workOrder, $result);
        Event::assertDispatched(WorkOrderStatusChanged::class);
    }

    /** @test */
    public function it_cancels_work_order()
    {
        $workOrder = WorkOrder::factory()->create([
            'status' => 'in_progress'
        ]);

        $this->workOrderRepository
            ->shouldReceive('update')
            ->once()
            ->with($workOrder->id, [
                'status' => 'cancelled',
                'cancelled_at' => Mockery::any()
            ])
            ->andReturn($workOrder);

        $result = $this->workOrderService->cancelWorkOrder($workOrder->id);

        $this->assertEquals($workOrder, $result);
        Event::assertDispatched(WorkOrderStatusChanged::class);
    }

    /** @test */
    public function it_adds_comment_to_work_order()
    {
        $workOrder = WorkOrder::factory()->create();
        $commentData = [
            'content' => 'Test comment',
            'user_id' => $this->user->id
        ];

        $this->workOrderRepository
            ->shouldReceive('addComment')
            ->once()
            ->with($workOrder->id, $commentData)
            ->andReturn($workOrder);

        $result = $this->workOrderService->addComment($workOrder->id, $commentData);

        $this->assertEquals($workOrder, $result);
    }

    /** @test */
    public function it_adds_attachment_to_work_order()
    {
        $workOrder = WorkOrder::factory()->create();
        $attachmentData = [
            'file' => 'test.pdf',
            'description' => 'Test attachment'
        ];

        $this->workOrderRepository
            ->shouldReceive('addAttachment')
            ->once()
            ->with($workOrder->id, $attachmentData)
            ->andReturn($workOrder);

        $result = $this->workOrderService->addAttachment($workOrder->id, $attachmentData);

        $this->assertEquals($workOrder, $result);
    }

    /** @test */
    public function it_calculates_work_order_duration()
    {
        $workOrder = WorkOrder::factory()->create([
            'started_at' => now()->subHours(5)
        ]);

        $this->workOrderRepository
            ->shouldReceive('find')
            ->once()
            ->with($workOrder->id)
            ->andReturn($workOrder);

        $duration = $this->workOrderService->calculateDuration($workOrder->id);

        $this->assertEquals(5, $duration);
    }

    /** @test */
    public function it_calculates_work_order_cost()
    {
        $workOrder = WorkOrder::factory()->create([
            'labor_cost' => 500,
            'material_cost' => 300,
            'additional_cost' => 200
        ]);

        $this->workOrderRepository
            ->shouldReceive('find')
            ->once()
            ->with($workOrder->id)
            ->andReturn($workOrder);

        $totalCost = $this->workOrderService->calculateTotalCost($workOrder->id);

        $this->assertEquals(1000, $totalCost);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 