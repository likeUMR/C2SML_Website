
<!DOCTYPE html>
<html>

<head>
    <title>会议登记系统 - 导入数据页面</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">导入数据</h1>
        <p style="color: red;">导入后原数据库将被完全覆盖！请慎重导入。</p>
        <form action="process_import.php" method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" class="border border-gray-300 p-2 w-full">
            <button type="submit" class="bg-blue-500 text-white p-2 mt-2">导入</button>
        </form>
    </div>
</body>

</html>