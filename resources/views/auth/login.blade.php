<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to Venusnap</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="icon">
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="apple-touch-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-[#7c3aed] p-6 text-white">
            <h1 class="text-2xl font-bold">Welcome to Venusnap</h1>
            <p class="text-[#d8b4fe] mt-1">Log in to continue</p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="p-6 space-y-6">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    class="block w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#7c3aed] focus:border-[#7c3aed]"
                    placeholder="you@example.com"
                >
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="block w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#7c3aed] focus:border-[#7c3aed]"
                    placeholder="••••••••"
                >
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#7c3aed] focus:ring-[#7c3aed]">
                    <span>Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-[#7c3aed] hover:underline">Forgot password?</a>
            </div>

            <!-- Login Button -->
            <button
                type="submit"
                class="w-full btn-primary text-white font-bold py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2"
            >
                Log In
            </button>

            <!-- Footer -->
            <p class="text-center text-gray-500 text-sm mt-4">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-[#7c3aed] hover:underline">Sign up</a>
            </p>
        </form>
    </div>

</body>
</html>
