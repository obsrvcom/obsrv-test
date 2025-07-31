<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col">
        <header class="bg-blue-800 text-white p-4">
            <h1 class="text-xl font-bold">Admin Panel</h1>
        </header>
        @include('components.layouts.admin.sidebar-admin', ['title' => $title ?? null])
        <main class="flex-1 p-8">
            @yield('content')
        </main>
    </div>
</body>
</html>
