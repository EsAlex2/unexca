<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNEXCA - Gestión Universitaria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body class="bg-gray-900 text-white font-sans">

    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-gray-800 border-r border-gray-700 hidden md:block">
            <div class="p-6 text-2xl font-bold text-blue-400">UNEXCA</div>
            <nav class="mt-6">
                <a href="#" class="flex items-center py-3 px-6 bg-blue-600 text-white rounded-lg mx-2 mb-2">
                    <i class="bi bi-speedometer2 me-3"></i> Dashboard
                </a>
                <a href="#"
                    class="flex items-center py-3 px-6 text-gray-400 hover:bg-gray-700 hover:text-white transition-all mx-2 rounded-lg">
                    <i class="bi bi-person-badge me-3"></i> Control de Estudios
                </a>
                <a href="#"
                    class="flex items-center py-3 px-6 text-gray-400 hover:bg-gray-700 hover:text-white transition-all mx-2 rounded-lg">
                    <i class="bi bi-journal-check me-3"></i> Notas y Actas
                </a>
            </nav>
        </aside>

        <main class="flex-1 overflow-y-auto p-8">
            <header class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-semibold">Resumen de Gestión</h1>
                <div class="flex items-center gap-4">
                    <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-sm">Sistema Online</span>
                    <button class="btn btn-outline-light btn-sm">Cerrar Sesión</button>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 hover:border-blue-500 transition-colors">
                    <h3 class="text-gray-400 mb-2">Inscritos Totales</h3>
                    <p class="text-4xl font-bold">1,240</p>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>