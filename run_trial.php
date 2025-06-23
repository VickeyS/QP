<?php
// run_trial.php: Handles form submission, runs trial.py, and displays results
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = __DIR__ . '/dashboard/teacher/upload/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $pdfPath = $uploadDir . basename($_FILES['pdf']['name']);
    if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfPath)) {
        die('File upload failed.');
    }
    $one = intval($_POST['one_marker']);
    $two = intval($_POST['two_marker']);
    $five = intval($_POST['five_marker']);
    $mcq = isset($_POST['mcq_count']) && $_POST['mcq_count'] !== '' ? intval($_POST['mcq_count']) : null;
    $pdfArg = escapeshellarg($pdfPath);
    $cmd = ($mcq !== null)
        ? "python trial.py $pdfArg $one $two $five $mcq"
        : "python trial.py $pdfArg $one $two $five";
    $output = shell_exec($cmd);
    $result = json_decode($output, true);
    echo '<h2>Generated Questions/MCQs</h2>';
    if (isset($result['error'])) {
        echo '<div style="color:red">Error: ' . htmlspecialchars($result['error']) . '</div>';
    } else {
        if (isset($result['questions'])) {
            echo '<h3>Questions</h3><ol>';
            foreach ($result['questions'] as $q) {
                echo '<li>' . htmlspecialchars($q) . '</li>';
            }
            echo '</ol>';
        }
        if (isset($result['mcqs'])) {
            echo '<h3>MCQs</h3><ol>';
            foreach ($result['mcqs'] as $mcq) {
                echo '<li>' . htmlspecialchars($mcq['question']) . '<ul>';
                foreach ($mcq['options'] as $idx => $opt) {
                    $label = chr(65 + $idx);
                    echo '<li>' . $label . '. ' . htmlspecialchars($opt) . '</li>';
                }
                echo '</ul><b>Answer: ' . htmlspecialchars($mcq['answer']) . '</b></li>';
            }
            echo '</ol>';
        }
    }
    echo '<a href="test_form.html">Back</a>';
}
?>
