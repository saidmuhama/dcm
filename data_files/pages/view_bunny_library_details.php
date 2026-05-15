<?php
// Bunny API config
$libraryId = "637820";
$apiKey = "1f983e59-1b03-4c98-aa21499d2cb3-58d9-40fc";
$url = "https://video.bunnycdn.com/library/$libraryId/videos";

// Initialize cURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "AccessKey: $apiKey",
        "accept: application/json"
    ],
]);

$response = curl_exec($ch);
$error = curl_error($ch);

curl_close($ch);

// Decode JSON
$data = json_decode($response, true);
?>
<div class="container mb-3 mt-3" id="main-content">

    <h3 class="mb-4">🎬 Video Library</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            Error: <?= $error ?>
        </div>
    <?php else: ?>

        <?php if (!empty($data['items'])): ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['items'] as $index => $video): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($video['title']) ?></td>
                                    <td><?= gmdate("H:i:s", $video['length'] ?? 0) ?></td>
                                    <td>
                                        <?php if ($video['status'] == 4): ?>
                                            <span class="badge bg-success">Ready</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Processing</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date("Y-m-d", strtotime($video['dateUploaded'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No videos found.</div>
        <?php endif; ?>

    <?php endif; ?>
</div>
