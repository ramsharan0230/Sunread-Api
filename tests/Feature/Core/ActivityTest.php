<?php
//
//namespace Tests\Feature\Core;
//
//use Illuminate\Foundation\Testing\RefreshDatabase;
//use Illuminate\Support\Str;
//use Modules\Core\Entities\ActivityLog;
//use Tests\AuthTestCase;
//
//
//class ActivityTest extends AuthTestCase
//{
//    use RefreshDatabase;
//
//    protected $admin;
//
//    protected $headers;
//
//    public function setUp(): void
//    {
//        parent::setUp();
//        $this->admin = $this->createAdmin([
//            'password' => 'password',
//        ]);
//    }
//
//    public function test_admin_can_fetch_activities()
//    {
//        $activity = factory(ActivityLog::class)->create([
//            'subject_id'
//        ]);
//        $response = $this->get(route('admin.activities.index'));
//        $response->assertJsonFragment([
//            "code" => $activity->code,
//            "name" => $activity->name
//        ]);
//    }
//
//    public function test_admin_can_fetch_a_activity()
//    {
//        $activity = factory(ActivityLog::class)->create();
//        $response = $this->get(route('admin.activities.show',$activity->id));
//        $response->assertJsonFragment([
//            "code" => $activity->code,
//            "name" => $activity->name
//        ]);
//    }
//
//    public function test_admin_can_delete_a_activity(){
//
//        $activity = factory(ActivityLog::class)->create();
//        $response = $this->delete(route('admin.activities.delete', $activity->id));
//        $response->assertJsonFragment([
//            "message" => "ActivityLog deleted successfully."
//        ]);
//
//    }
//
//}
