<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
include_once 'sql/queries.php';

startSecureSession();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare($sql_check_user);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // ලොගින් සාර්ථකයි
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Last login update
            $update = $pdo->prepare($sql_update_login_time);
            $update->execute(['id' => $user['id']]);

            header("Location: index.php");
            exit;
        } else {
            $error = "පරිශීලක නාමය හෝ මුරපදය වැරදියි!";
        }
    } else {
        $error = "කරුණාකර සියලු විස්තර පුරවන්න.";
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title>WaNTg - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#060912] flex items-center justify-center min-h-screen p-4 md:p-8">
    <div class="w-full max-w-sm md:max-w-md bg-[#0d111c] border border-gray-800 p-6 md:p-10 rounded-[2.5rem] shadow-2xl">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-white mb-2">WaNTg</h1>
            <p class="text-gray-500 text-sm">Secure Management Portal</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl mb-6 text-sm text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Username</label>
                <input type="text" name="username" required
                       class="w-full bg-gray-900/50 border border-gray-800 rounded-xl px-4 py-3 text-white focus:border-blue-500 focus:outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full bg-gray-900/50 border border-gray-800 rounded-xl px-4 py-3 text-white focus:border-blue-500 focus:outline-none transition-all">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/20 transition-all transform active:scale-95">
                Login to Dashboard
            </button>
        </form>

        <p class="text-center text-gray-600 text-xs mt-8 italic">
            Developed by erandiya
        </p>
    </div>

</body>
</html>