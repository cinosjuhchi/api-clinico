<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Impor Excel</title>
</head>
<body>
    <div class="max-w-md mx-auto my-12 p-6 bg-white shadow-md rounded-lg">
    <h1 class="text-2xl font-bold mb-4">Impor Data Excel</h1>
    <form method="POST" class="space-y-4" enctype="multipart/form-data" action="{{ route('import') }}">
        @csrf
      <div>
        <label for="excel-file" class="block font-medium mb-1">Pilih file Excel</label>
        <input type="file" id="excel_file" name="excel-file" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring focus:border-blue-500" required>
      </div>
      <div>
        <label for="sheet-name" class="block font-medium mb-1">Nama Sheet</label>
        <input type="text" id="sheet-name" name="sheet-name" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring focus:border-blue-500" required>
      </div>
      <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring focus:border-blue-500">
        Impor Data
      </button>
    </form>
  </div>
</body>
</html>