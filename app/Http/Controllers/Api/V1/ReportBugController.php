<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportBugRequest;
use App\Mail\ReportBugMail;
use App\Models\ReportBug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReportBugController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->query('q');
            $query = ReportBug::query();
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', '%' . $search . '%')
                      ->orWhere('note', 'like', '%' . $search . '%')
                      ->orWhere('status', 'like', '%' . $search . '%');
                });
            }
            $reportBugs = $query->orderBy('status')->paginate(10);
            return response()->json([
                "status" => "success",
                "message" => "Get report bugs successfully",
                "data" => $reportBugs
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching report bugs: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Failed to fetch report bugs",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreReportBugRequest $request)
    {
        try {
            $reportBug = ReportBug::create($request->validated());
            return response()->json([
                "status" => "success",
                "message" => "Report bug created successfully",
                "data" => $reportBug
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error creating report bug: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Failed to create report bug",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $reportBug = ReportBug::findOrFail($id);
            return response()->json([
                "status" => "success",
                "message" => "Get report bug details successfully",
                "data" => $reportBug
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching report bug details: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Report bug not found",
                "error" => $e->getMessage()
            ], 404);
        }
    }

    public function update(StoreReportBugRequest $request, $id)
    {
        try {
            $reportBug = ReportBug::findOrFail($id);
            $reportBug->update($request->validated());
            return response()->json([
                "status" => "success",
                "message" => "Report bug updated successfully",
                "data" => $reportBug
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating report bug: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Failed to update report bug",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $reportBug = ReportBug::findOrFail($id);
            $reportBug->delete();
            return response()->json([
                "status" => "success",
                "message" => "Report bug deleted successfully",
                "data" => null
            ], 204);
        } catch (\Exception $e) {
            Log::error("Error deleting report bug: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Failed to delete report bug",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function sendEmail(Request $request, $id)
    {
        try {
            $request->validate([
                'subject' => 'required|string',
                'message' => 'required|string',
            ]);

            $reportBug = ReportBug::with("reportBugType")->findOrFail($id);

            Mail::to($reportBug->email)
                ->send(new ReportBugMail($reportBug, $request->subject, $request->message));

            return response()->json([
                "status" => "success",
                "message" => "Email sent successfully",
                "data" => null
            ]);
        } catch (\Exception $e) {
            Log::error("Error sending email: " . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Failed to send email",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function resolve($id)
    {
        try {
            $reportBug = ReportBug::findOrFail($id);
            $reportBug->status = "resolved";
            $reportBug->save();
            return response()->json([
                "status" => "success",
                "message" => "Report bug resolved successfully",
                "data" => $reportBug
            ]);
        } catch (\Exception $e) {
            Log::error("Error resolving report bug: ". $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Failed to resolve report bug",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
