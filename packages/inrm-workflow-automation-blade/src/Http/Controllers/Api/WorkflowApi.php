<?php
namespace Inrm\Workflow\Http\Controllers\Api;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Inrm\Workflow\Support\Engine;

class WorkflowApi extends Controller
{
    public function runDue() { return (new Engine())->runDue(); }
    public function runOne($id) { return (new Engine())->runAutomation((int)$id); }
}
