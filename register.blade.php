<!DOCTYPE html>
<html>

<head>
    <title>会议登记系统 - 注册</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-md">
            <div class="bg-white p-8 rounded shadow-md">
                <h2 class="text-2xl font-semibold mb-4">注册</h2>
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <strong>错误：</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('register.submit') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700">姓名</label>
                        <input type="text" id="name" name="name"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="school" class="block text-gray-700">学校</label>
                        <input type="text" id="school" name="school"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700">邮箱</label>
                        <input type="email" id="email" name="email"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700">职称</label>
                        <select id="title" name="title" class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                            <option value="老师">老师</option>
                            <option value="学生">学生</option>
                            <option value="其它">其它</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="department" class="block text-gray-700">院系</label>
                        <input type="text" id="department" name="department"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700">手机</label>
                        <input type="text" id="phone" name="phone"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="supervisor" class="block text-gray-700">导师</label>
                        <input type="text" id="supervisor" name="supervisor"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">注册</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>