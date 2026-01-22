<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - {{ $file->original_name }}</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .preview-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .file-header {
            border-bottom: 2px solid #11998e;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .file-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .file-actions {
            display: flex;
            gap: 10px;
        }
        .preview-content {
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        iframe {
            width: 100%;
            height: 80vh;
            border: none;
        }
        img {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="file-header">
            <div class="file-info">
                <div>
                    <h3 class="mb-1">{{ $file->original_name }}</h3>
                    <small class="text-muted">
                        <i class="bx bx-calendar"></i> {{ $file->created_at->format('M d, Y h:i A') }} | 
                        <i class="bx bx-data"></i> {{ number_format($file->file_size / 1024, 2) }} KB
                    </small>
                </div>
                <div class="file-actions">
                    <a href="{{ Storage::disk('public')->url($file->file_path) }}" download="{{ $file->original_name }}" class="btn btn-success">
                        <i class="bx bx-download"></i> Download
                    </a>
                    <button onclick="window.close()" class="btn btn-secondary">
                        <i class="bx bx-x"></i> Close
                    </button>
                </div>
            </div>
        </div>
        <div class="preview-content">
            @php
                $filePath = Storage::disk('public')->url($file->file_path);
                $extension = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
                $mimeType = $file->mime_type ?? Storage::disk('public')->mimeType($file->file_path);
            @endphp
            
            @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']) || strpos($mimeType, 'image/') === 0)
                <img src="{{ $filePath }}" alt="{{ $file->original_name }}">
            @elseif($extension === 'pdf' || $mimeType === 'application/pdf')
                <iframe src="{{ $filePath }}"></iframe>
            @else
                <div class="text-center">
                    <i class="bx bx-file fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Preview Not Available</h5>
                    <p class="text-muted">Preview is not available for this file type.</p>
                    <a href="{{ Storage::disk('public')->url($file->file_path) }}" download="{{ $file->original_name }}" class="btn btn-success">
                        <i class="bx bx-download me-1"></i>Download to View
                    </a>
                </div>
            @endif
        </div>
    </div>
</body>
</html>

