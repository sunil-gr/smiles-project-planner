<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
include 'header.php';

// Directory for uploads and responses
$upload_dir = __DIR__ . '/upload/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Stepper state
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1;
}
$step = $_SESSION['step'];

$doc_id = isset($_SESSION['doc_id']) ? $_SESSION['doc_id'] : '';
$audio_id = isset($_SESSION['audio_id']) ? $_SESSION['audio_id'] : '';
$doc_file = $upload_dir . 'document.json';
$audio_file = $upload_dir . 'audio.json';
$workflow_response = '';

// Handle navigation
if (isset($_POST['back'])) {
    if ($step > 1) {
        $_SESSION['step'] = $step - 1;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Upload document
    if (isset($_FILES['file']) && $step === 1) {
        $filePath = $_FILES['file']['tmp_name'];
        $fileName = basename($_FILES['file']['name']);
        $user = 'abc-123';
        $apiKey = 'app-nRYeinE3bte2rDVxgoyGiAjw';
        $local_doc_path = $upload_dir . 'uploaded_document_' . $fileName;
        move_uploaded_file($filePath, $local_doc_path);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.dify.ai/v1/files/upload",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $apiKey"
            ],
            CURLOPT_POSTFIELDS => [
                'file' => new CURLFile($local_doc_path, mime_content_type($local_doc_path), $fileName),
                'user' => $user
            ]
        ]);
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = 'Curl error: ' . curl_error($curl);
        }
        curl_close($curl);
        file_put_contents($doc_file, $response);
        // Store all document upload responses in session
        if (!isset($_SESSION['doc_responses'])) {
            $_SESSION['doc_responses'] = [];
        }
        $_SESSION['doc_responses'][] = [
            'filename' => $fileName,
            'response' => $response,
            'time' => date('c')
        ];
        $data = json_decode($response, true);
        if (isset($data['id'])) {
            $_SESSION['doc_id'] = $data['id'];
            $_SESSION['doc_name'] = $fileName;
            $_SESSION['doc_local'] = basename($local_doc_path);
            $_SESSION['step'] = 2;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    // Step 2: Upload audio
    if (isset($_FILES['audio']) && $step === 2) {
        $audioPath = $_FILES['audio']['tmp_name'];
        $audioName = basename($_FILES['audio']['name']);
        $user = 'abc-123';
        $apiKey = 'app-nRYeinE3bte2rDVxgoyGiAjw';
        $local_audio_path = $upload_dir . 'uploaded_audio_' . $audioName;
        move_uploaded_file($audioPath, $local_audio_path);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.dify.ai/v1/files/upload",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $apiKey"
            ],
            CURLOPT_POSTFIELDS => [
                'file' => new CURLFile($local_audio_path, mime_content_type($local_audio_path), $audioName),
                'user' => $user
            ]
        ]);
        $audio_response = curl_exec($curl);
        if (curl_errno($curl)) {
            $audio_response = 'Curl error: ' . curl_error($curl);
        }
        curl_close($curl);
        file_put_contents($audio_file, $audio_response);
        // Store all audio upload responses in session
        if (!isset($_SESSION['audio_responses'])) {
            $_SESSION['audio_responses'] = [];
        }
        $_SESSION['audio_responses'][] = [
            'filename' => $audioName,
            'response' => $audio_response,
            'time' => date('c')
        ];
        $audio_data = json_decode($audio_response, true);
        if (isset($audio_data['id'])) {
            $_SESSION['audio_id'] = $audio_data['id'];
            $_SESSION['audio_name'] = $audioName;
            $_SESSION['audio_local'] = basename($local_audio_path);
            $_SESSION['step'] = 3;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    // Step 3: Run workflow
    if (isset($_POST['run_workflow']) && $step === 3) {
        $workflow_id = 'QH7a00LXdLpDs13o';
        $requirement = 'users tories and sub tasks';
        $user = 'abc-123';
        $apiKey = 'app-nRYeinE3bte2rDVxgoyGiAjw';
        $doc_id = $_SESSION['doc_id'];
        $audio_id = $_SESSION['audio_id'];
        $workflow_payload = [
            "workflow_id" => $workflow_id,
            "inputs" => [
                "Documents" => [[
                    "type" => "document",
                    "transfer_method" => "local_file",
                    "url" => "",
                    "upload_file_id" => $doc_id
                ]],
                "requirment" => $requirement,
                "meeting_recording" => [
                    "type" => "audio",
                    "transfer_method" => "local_file",
                    "url" => "",
                    "upload_file_id" => $audio_id
                ]
            ],
            "response_mode" => "blocking",
            "user" => $user
        ];
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.dify.ai/v1/workflows/run",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $apiKey",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($workflow_payload)
        ]);
        $workflow_response = curl_exec($curl);
        if (curl_errno($curl)) {
            $workflow_response = 'Curl error: ' . curl_error($curl);
        }
        curl_close($curl);
        file_put_contents($upload_dir . 'run_workflow.json', $workflow_response);
        $_SESSION['workflow_response'] = $workflow_response;
        $_SESSION['step'] = 4;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    // Step 4: Reset
    if (isset($_POST['reset'])) {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    // Add PHP handler for 'clear' button to reset stepper and move to initial step
    if (isset($_POST['clear'])) {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// For displaying details
$doc_json = file_exists($doc_file) ? json_decode(file_get_contents($doc_file), true) : null;
$audio_json = file_exists($audio_file) ? json_decode(file_get_contents($audio_file), true) : null;
$workflow_response = isset($_SESSION['workflow_response']) ? $_SESSION['workflow_response'] : '';

function stepper($step) {
    $steps = [
        1 => ['Upload Document', '📄'],
        2 => ['Upload Audio', '🎤'],
        3 => ['Review & Run Workflow', '⚙️'],
        4 => ['Result', '✅']
    ];
    echo '<div class="stepper">';
    $i = 1;
    foreach ($steps as $num => $info) {
        $label = $info[0];
        $icon = $info[1];
        $active = $step == $num ? 'active' : ($step > $num ? 'done' : '');
        echo '<div class="step ' . $active . '"><span class="step-icon">' . $icon . '</span><span class="step-label">' . $label . '</span></div>';
        if ($i < count($steps)) echo '<div class="step-line"></div>';
        $i++;
    }
    echo '</div>';
}

function parse_markdown_json($str) {
    // Extract JSON from markdown code block
    if (preg_match('/```json\\n([\s\S]+?)```/', $str, $matches)) {
        $json = $matches[1];
        $decoded = json_decode($json, true);
        if ($decoded) return $decoded;
    }
    // Try direct JSON decode as fallback
    $decoded = json_decode($str, true);
    return $decoded ? $decoded : null;
}

function render_kanban_from_ai_output($ai_output) {
    if (!is_array($ai_output)) return false;
    $summary = isset($ai_output['summary']) ? $ai_output['summary'] : '';
    $epic = isset($ai_output['epic']) ? $ai_output['epic'] : '';
    $epic_desc = isset($ai_output['epic_desc']) ? $ai_output['epic_desc'] : '';
    $kanban = isset($ai_output['kanban']) ? $ai_output['kanban'] : [];
    if (!$kanban || !is_array($kanban)) return false;
    echo '<div class="kanban-container">';
    if ($summary) {
        echo '<div class="kanban-summary"><strong>Discussion Summary:</strong> ' . htmlspecialchars($summary) . '</div>';
    }
    if ($epic) {
        echo '<div class="kanban-epic-title">Epic: ' . htmlspecialchars($epic) . '</div>';
    }
    if ($epic_desc) {
        echo '<div class="kanban-epic-desc">' . htmlspecialchars($epic_desc) . '</div>';
    }
    echo '<div class="kanban-board">';
    foreach (["To Do", "In Progress", "Done"] as $col) {
        echo '<div class="kanban-column">';
        echo '<div class="kanban-column-title">' . htmlspecialchars($col) . '</div>';
        if (isset($kanban[$col]) && is_array($kanban[$col]) && count($kanban[$col])) {
            foreach ($kanban[$col] as $card) {
                echo '<div class="kanban-card">';
                if (isset($card['title']))
                    echo '<div class="kanban-card-title">' . htmlspecialchars($card['title']) . '</div>';
                if (isset($card['details']))
                    echo '<div class="kanban-card-details">' . htmlspecialchars($card['details']) . '</div>';
                if (isset($card['estimate']) || isset($card['story_points'])) {
                    echo '<div class="kanban-card-meta">';
                    if (isset($card['estimate']))
                        echo '<span>Estimate: ' . htmlspecialchars($card['estimate']) . '</span>';
                    if (isset($card['story_points']))
                        echo '<span class="kanban-story-points">' . htmlspecialchars($card['story_points']) . ' SP</span>';
                    echo '</div>';
                }
                echo '</div>';
            }
        }
        echo '</div>';
    }
    echo '</div></div>';
    return true;
}

function render_kanban_from_ai_output_v2($ai_output) {
    // Expecting keys: discussion_summary, epics (array of epic_label, description, sub_tasks[])
    if (!is_array($ai_output) || !isset($ai_output['epics'][0])) return false;
    $summary = isset($ai_output['discussion_summary']) ? $ai_output['discussion_summary'] : '';
    $epic = isset($ai_output['epics'][0]['epic_label']) ? $ai_output['epics'][0]['epic_label'] : '';
    $epic_desc = isset($ai_output['epics'][0]['description']) ? $ai_output['epics'][0]['description'] : '';
    $sub_tasks = isset($ai_output['epics'][0]['sub_tasks']) ? $ai_output['epics'][0]['sub_tasks'] : [];
    // Map all sub_tasks to To Do for simplicity
    $kanban = [
        'To Do' => [],
        'In Progress' => [],
        'Done' => []
    ];
    foreach ($sub_tasks as $task) {
        $kanban['To Do'][] = [
            'title' => $task['ticket_name'] ?? '',
            'details' => $task['details'] ?? '',
            'estimate' => $task['time_estimate'] ?? '',
            'story_points' => $task['story_points'] ?? ''
        ];
    }
    // Use the same kanban rendering as before
    return render_kanban_from_ai_output([
        'summary' => $summary,
        'epic' => $epic,
        'epic_desc' => $epic_desc,
        'kanban' => $kanban
    ]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smiles Project Planner </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script>
    // Loader logic
    function showLoader() {
        document.getElementById('loader-overlay').style.display = 'flex';
    }
    function hideLoader() {
        document.getElementById('loader-overlay').style.display = 'none';
    }
    // Attach loader to all forms that submit files or run workflow
    window.addEventListener('DOMContentLoaded', function() {
        var forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                // Only show loader for file uploads or workflow run
                var hasFile = form.querySelector('input[type="file"]');
                var hasRunWorkflow = form.querySelector('button[name="run_workflow"]');
                if (hasFile || hasRunWorkflow) {
                    showLoader();
                }
            });
        });

        // Simple Kanban drag-and-drop
        const columns = document.querySelectorAll('.kanban-column');
        let draggedCard = null;

        document.querySelectorAll('.kanban-card').forEach(card => {
            card.setAttribute('draggable', 'true');
            card.addEventListener('dragstart', function(e) {
                draggedCard = this;
                setTimeout(() => this.classList.add('dragging'), 0);
            });
            card.addEventListener('dragend', function(e) {
                draggedCard = null;
                this.classList.remove('dragging');
            });
        });
        columns.forEach(col => {
            col.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
            col.addEventListener('dragleave', function(e) {
                this.classList.remove('drag-over');
            });
            col.addEventListener('drop', function(e) {
                this.classList.remove('drag-over');
                if (draggedCard) {
                    this.appendChild(draggedCard);
                }
            });
        });
    });
    </script>
</head>
<body>
    <div id="loader-overlay" class="loader-overlay"><div class="loader"></div></div>
    <div class="container">
        <?php stepper($step); ?>
        <?php if ($step === 1): ?>
            <form method="post" enctype="multipart/form-data">
                <label for="file">Drop your technical documents here:</label>
                <input type="file" name="file" id="file">
                <div class="actions">
                    <button type="submit">Upload Document</button>
                </div>
            </form>
        <?php elseif ($step === 2): ?>
            <div class="details details-grid">
                <div class="details-col">
                    <div class="details-title">Document uploaded!</div>
                    <div class="details-item"><span class="details-label">Name:</span> <?php echo htmlspecialchars($_SESSION['doc_name']); ?></div>
                    <div class="details-item"><span class="details-label">ID:</span> <code><?php echo htmlspecialchars($_SESSION['doc_id']); ?></code></div>
                </div>
                <div class="details-col details-files">
                    <div class="details-files-title">View Files</div>
                    <a class="file-link" href="upload/<?php echo htmlspecialchars($_SESSION['doc_local']); ?>" target="_blank">📄 Local Copy</a>
                    <a class="file-link" href="upload/document.json" target="_blank">🗂️ document.json</a>
                </div>
            </div>
            <form method="post" enctype="multipart/form-data">
                <label for="audio">Upload your meeting recordings:</label>
                <input type="file" name="audio" id="audio" accept="audio/*">
                <div class="actions">
                    <button type="submit">Upload Audio</button>
                    <form method="post" style="display:inline;"><button type="submit" name="back" class="back-btn">Back</button></form>
                    <form method="post" style="display:inline;"><button type="submit" name="clear" class="clear-btn">Clear</button></form>
                </div>
            </form>
        <?php elseif ($step === 3): ?>
            <div class="details details-grid">
                <div class="details-col">
                    <div class="details-title">Audio uploaded!</div>
                    <div class="details-item"><span class="details-label">Name:</span> <?php echo htmlspecialchars($_SESSION['audio_name']); ?></div>
                    <div class="details-item"><span class="details-label">ID:</span> <code><?php echo htmlspecialchars($_SESSION['audio_id']); ?></code></div>
                </div>
                <div class="details-col details-files">
                    <div class="details-files-title">View Files</div>
                    <a class="file-link" href="upload/<?php echo htmlspecialchars($_SESSION['audio_local']); ?>" target="_blank">🎤 Local Copy</a>
                    <a class="file-link" href="upload/audio.json" target="_blank">🗂️ audio.json</a>
                </div>
            </div>
            <form method="post">
                <div class="actions">
                    <button type="submit" name="run_workflow">Run Agent</button>
                    <form method="post" style="display:inline;"><button type="submit" name="back" class="back-btn">Back</button></form>
                    <form method="post" style="display:inline;"><button type="submit" name="clear" class="clear-btn">Clear</button></form>
                </div>
            </form>
        <?php elseif ($step === 4): ?>
            <div class="response">
                <!-- <strong>Workflow API Response:</strong> -->
                <?php
                $workflow_json = json_decode($workflow_response, true);
                $ai_output = isset($workflow_json['data']['outputs']['AI_output']) ? $workflow_json['data']['outputs']['AI_output'] : null;
                if (!empty($ai_output)) {
                    if (is_string($ai_output)) {
                        $ai_output = parse_markdown_json($ai_output);
                    }
                    if (is_array($ai_output) && render_kanban_from_ai_output_v2($ai_output)) {
                        // Kanban rendered
                    } else {
                        echo '<div class="json-box">AI_output is present but not in expected format.<br>';
                        echo nl2br(htmlspecialchars(json_encode($ai_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)));
                        echo '</div>';
                    }
                } else {
                    echo '<div class="json-box">AI_output is empty or missing.</div>';
                }
                ?>
            </div>
            <div class="actions">
                <form method="post"><button type="submit" name="back" class="back-btn">Back</button></form>
                <form method="post"><button type="submit" name="reset">Start Over</button></form>
                <form method="post"><button type="submit" name="clear" class="clear-btn">Clear</button></form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php include 'footer.php'; ?> 