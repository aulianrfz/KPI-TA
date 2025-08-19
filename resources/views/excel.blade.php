<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <title>Import Excel</title>
</head>
<body>
    <form action="{{ route('import_excel_post') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="file">File</label>
            <input type="file" class="form-control-file" name="excel_file" id="file">
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</body>
</html>
