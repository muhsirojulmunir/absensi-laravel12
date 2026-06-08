<?php
$evals = App\Models\PerformanceEvaluation::with('user')->get();
foreach ($evals as $e) {
    echo "=== " . $e->user->name . " ===\n";
    echo "Predicate: " . $e->predicate . "\n";
    echo "Hadir: " . ($e->attendance_summary['total_hadir'] ?? 0) . "\n";
    echo "Feedback: " . $e->ai_feedback . "\n";
    echo "Recommendation: " . $e->ai_recommendation . "\n\n";
}
