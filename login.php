<?php
session_start();
require_once 'config.php';

$page_title = 'Login';

// --- LOGIKA PHP ANDA (TIDAK DIUBAH) ---
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'asisten') {
        header('Location: asisten/dashboard.php');
    } else {
        header('Location: mahasiswa/dashboard.php');
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'asisten') {
                header('Location: asisten/dashboard.php');
            } else {
                header('Location: mahasiswa/dashboard.php');
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
// --- AKHIR LOGIKA PHP ---
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --retro-border: #4d4d4d;
        }
        body {
            font-family: 'Public Sans', sans-serif;
        }
        .font-serif {
            font-family: 'DM Serif Display', serif;
        }
        .retro-shadow {
            box-shadow: 8px 8px 0px var(--retro-border);
        }
        .retro-input {
            appearance: none;
            border-radius: 0;
            border: 2px solid var(--retro-border);
            padding: 0.75rem;
            width: 100%;
            background-color: #fffbeb;
        }
        .retro-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px #f59e0b; /* Yellow focus */
            border-color: #f59e0b;
        }
        .retro-btn {
            border-radius: 0;
            border: 2px solid var(--retro-border);
            font-weight: 700;
            padding: 0.75rem;
            width: 100%;
            color: white;
            background-color: var(--retro-border);
            transition: all 0.2s ease;
        }
        .retro-btn:hover {
            background-color: #3c3c3c;
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0px var(--retro-border);
        }
    </style>
</head>
<body class="bg-amber-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full space-y-6">
        <div class="text-center">
            <h1 class="font-serif text-5xl font-bold text-amber-900">SIMPRAK</h1>
            <p class="text-stone-700 mt-1 text-lg">Sistem Informasi Manajemen Praktikum</p>
        </div>
        
        <div class="bg-white border-2 border-stone-800 p-8 retro-shadow">
            <div class="text-center mb-8">
                <h2 class="font-serif text-3xl font-bold text-amber-900">Login Akun</h2>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-red-100 border-2 border-red-800 text-red-900 rounded-none">
                    <i class="fas fa-exclamation-triangle mr-2"></i><span class="font-bold"><?= $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-bold text-stone-800 mb-2">
                        Alamat Email
                    </label>
                    <input type="email" id="email" name="email" required 
                           class="retro-input"
                           placeholder="contoh@email.com">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-bold text-stone-800 mb-2">
                        Password
                    </label>
                    <input type="password" id="password" name="password" required 
                           class="retro-input"
                           placeholder="••••••••">
                </div>
                
                <button type="submit" class="retro-btn">
                    <i class="fas fa-sign-in-alt mr-2"></i>LOGIN
                </button>
            </form>
            
            <div class="text-center mt-6 pt-6 border-t-2 border-dashed border-stone-300">
                <p class="text-stone-700">
                    Belum punya akun? 
                    <a href="register.php" class="text-amber-800 hover:text-amber-900 font-bold underline">
                        Daftar di sini
                    </a>
                </p>
            </div>
        </div>
        
        <div class="text-center">
            <a href="index.php" class="text-stone-700 hover:text-amber-900 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>