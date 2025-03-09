<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\WorkOrderRepository;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Asset;
use App\Models\Comment;
use App\Models\Attachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkOrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private WorkOrderRepository $workOrderRepository;
    private User $user;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->workOrderRepository = new WorkOrderRepository();
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create();
    }

    /** @test */
    public function it_can_create_work_order()
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

        $workOrder = $this->workOrderRepository->create($data);

        $this->assertInstanceOf(WorkOrder::class, $workOrder);
        $this->assertEquals('WO001', $workOrder->code);
        $this->assertEquals('Test Work Order', $workOrder->title);
    }

    /** @test */
    public function it_can_update_work_order()
    {
        $workOrder = WorkOrder::factory()->create();
        $updateData = [
            'title' => 'Updated Work Order',
            'status' => 'in_progress'
        ];

        $updatedWorkOrder = $this->workOrderRepository->update($workOrder->id, $updateData);

        $this->assertEquals('Updated Work Order', $updatedWorkOrder->title);
        $this->assertEquals('in_progress', $updatedWorkOrder->status);
    }

    /** @test */
    public function it_can_delete_work_order()
    {
        $workOrder = WorkOrder::factory()->create();

        $this->workOrderRepository->delete($workOrder->id);

        $this->assertSoftDeleted('work_orders', ['id' => $workOrder->id]);
    }

    /** @test */
    public function it_can_find_work_order_by_id()
    {
        $workOrder = WorkOrder::factory()->create();

        $foundWorkOrder = $this->workOrderRepository->find($workOrder->id);

        $this->assertEquals($workOrder->id, $foundWorkOrder->id);
    }

    /** @test */
    public function it_can_get_all_work_orders()
    {
        WorkOrder::factory()->count(3)->create();

        $workOrders = $this->workOrderRepository->all();

        $this->assertCount(3, $workOrders);
    }

    /** @test */
    public function it_can_get_work_orders_by_status()
    {
        WorkOrder::factory()->count(2)->create(['status' => 'open']);
        WorkOrder::factory()->create(['status' => 'completed']);

        $openWorkOrders = $this->workOrderRepository->getByStatus('open');

        $this->assertCount(2, $openWorkOrders);
        $this->assertTrue($openWorkOrders->every(fn ($wo) => $wo->status === 'open'));
    }

    /** @test */
    public function it_can_get_work_orders_by_priority()
    {
        WorkOrder::factory()->count(2)->create(['priority' => 'high']);
        WorkOrder::factory()->create(['priority' => 'low']);

        $highPriorityWorkOrders = $this->workOrderRepository->getByPriority('high');

        $this->assertCount(2, $highPriorityWorkOrders);
        $this->assertTrue($highPriorityWorkOrders->every(fn ($wo) => $wo->priority === 'high'));
    }

    /** @test */
    public function it_can_get_work_orders_by_asset()
    {
        WorkOrder::factory()->count(2)->create(['asset_id' => $this->asset->id]);
        WorkOrder::factory()->create();

        $assetWorkOrders = $this->workOrderRepository->getByAsset($this->asset->id);

        $this->assertCount(2, $assetWorkOrders);
        $this->assertTrue($assetWorkOrders->every(fn ($wo) => $wo->asset_id === $this->asset->id));
    }

    /** @test */
    public function it_can_get_work_orders_by_assigned_user()
    {
        WorkOrder::factory()->count(2)->create(['assigned_to' => $this->user->id]);
        WorkOrder::factory()->create();

        $userWorkOrders = $this->workOrderRepository->getByAssignedUser($this->user->id);

        $this->assertCount(2, $userWorkOrders);
        $this->assertTrue($userWorkOrders->every(fn ($wo) => $wo->assigned_to === $this->user->id));
    }

    /** @test */
    public function it_can_add_comment_to_work_order()
    {
        $workOrder = WorkOrder::factory()->create();
        $commentData = [
            'content' => 'Test comment',
            'user_id' => $this->user->id
        ];

        $comment = $this->workOrderRepository->addComment($workOrder->id, $commentData);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('Test comment', $comment->content);
        $this->assertEquals($this->user->id, $comment->user_id);
    }

    /** @test */
    public function it_can_add_attachment_to_work_order()
    {
        Storage::fake('local');

        $workOrder = WorkOrder::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $attachmentData = [
            'file' => $file,
            'description' => 'Test attachment'
        ];

        $attachment = $this->workOrderRepository->addAttachment($workOrder->id, $attachmentData);

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals('Test attachment', $attachment->description);
        Storage::disk('local')->assertExists('attachments/' . $file->hashName());
    }

    /** @test */
    public function it_can_get_overdue_work_orders()
    {
        WorkOrder::factory()->count(2)->create([
            'due_date' => now()->subDay(),
            'status' => 'in_progress'
        ]);
        WorkOrder::factory()->create([
            'due_date' => now()->addDay()
        ]);

        $overdueWorkOrders = $this->workOrderRepository->getOverdueWorkOrders();

        $this->assertCount(2, $overdueWorkOrders);
        $this->assertTrue($overdueWorkOrders->every(fn ($wo) => $wo->due_date < now()));
    }

    /** @test */
    public function it_can_get_work_orders_due_today()
    {
        WorkOrder::factory()->count(2)->create([
            'due_date' => now()->toDateString()
        ]);
        WorkOrder::factory()->create([
            'due_date' => now()->addDay()
        ]);

        $todayWorkOrders = $this->workOrderRepository->getWorkOrdersDueToday();

        $this->assertCount(2, $todayWorkOrders);
        $this->assertTrue($todayWorkOrders->every(fn ($wo) => $wo->due_date === now()->toDateString()));
    }

    /** @test */
    public function it_can_get_work_order_statistics()
    {
        // Create work orders with different statuses
        WorkOrder::factory()->count(2)->create(['status' => 'open']);
        WorkOrder::factory()->count(3)->create(['status' => 'in_progress']);
        WorkOrder::factory()->create(['status' => 'completed']);

        $stats = $this->workOrderRepository->getStatistics();

        $this->assertEquals(6, $stats['total']);
        $this->assertEquals(2, $stats['open']);
        $this->assertEquals(3, $stats['in_progress']);
        $this->assertEquals(1, $stats['completed']);
    }

    /** @test */
    public function it_can_search_work_orders()
    {
        WorkOrder::factory()->create(['title' => 'Test Work Order ABC']);
        WorkOrder::factory()->create(['title' => 'Another Work Order XYZ']);
        WorkOrder::factory()->create(['title' => 'Different Task']);

        $searchResults = $this->workOrderRepository->search('Work Order');

        $this->assertCount(2, $searchResults);
        $this->assertTrue($searchResults->contains('title', 'Test Work Order ABC'));
        $this->assertTrue($searchResults->contains('title', 'Another Work Order XYZ'));
    }
} 