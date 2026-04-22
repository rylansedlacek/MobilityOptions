<?php
// Comment for assignment -Madi
// Template for new VMS pages. Base your new page on this one

// Make session information accessible, allowing us to associate
// data with the logged-in user.
session_cache_expire(30);
session_start();

ini_set("display_errors", 1);
error_reporting(E_ALL);

// redirect to index if already logged in
if (isset($_SESSION['_id'])) {
    header('Location: index.php');
    die();
}
$badLogin = false;
$archivedAccount = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once('include/input-validation.php');
    $ignoreList = array('password');
    $args = sanitize($_POST, $ignoreList);
    $required = array('username', 'password');
    if (wereRequiredFieldsSubmitted($args, $required)) {
        require_once('domain/Person.php');
        require_once('database/dbPersons.php');
        /*@require_once('database/dbMessages.php');*/
        /*@dateChecker();*/
        $username = strtolower($args['username']);
        $password = $args['password'];
        $user = retrieve_person($username);
        if (!$user) {
            $badLogin = true;
        } /*else if ($user->get_status() === "Inactive") {
                // If the user is archived, block login
                $archivedAccount = true;
            }*/ else if (password_verify($password, $user->get_password())) {
            $_SESSION['logged_in'] = true;

            $_SESSION['access_level'] = $user->get_access_level();
            $_SESSION['f_name'] = $user->get_first_name();
            $_SESSION['l_name'] = $user->get_last_name();


            $_SESSION['type'] = 'admin';
            $_SESSION['_id'] = $user->get_id();

            //hard code root privileges
            if ($user->get_id() == 'vmsroot') {
                $_SESSION['access_level'] = 3;
                $_SESSION['locked'] = false;
                header('Location: index.php');
            }

            else {
                header('Location: index.php');
                die();
            }
            die();
        } else {
            $badLogin = true;
        }
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: Quicksand, sans-serif;
        }
    </style>
    <title>Log In | Healthy Generations</title>
</head>

<body class="overflow-hidden">
    <div class="min-h-screen relative">

        <div class="absolute inset-0">
            <img src="images/l.jpeg"
                alt="Barrels"
                style="height: 100%;"
                class="w-full h-full object-cover">
        </div>

        <div class="absolute inset-0 bg-black/5"></div>

        <div class="relative min-h-screen flex items-center justify-center ">

            <div class="w-full max-w-3xl h-[950px] bg-white/25 backdrop-blur-md p-7 rounded-3xl shadow-2xl ">


                <div class="w-full flex justify-center mb-6">
                    <img src="images/healthyGenerations.png" alt="Logo" class="w-full max-w-xs">
                </div>

                <form class="w-full" method="post">
                    <?php
                    if ($badLogin) {
                        echo '<span class="text-white bg-red-700 text-center block p-2 rounded-lg mb-2">No login with that username and password combination currently exists.</span>';
                    }
                    if ($archivedAccount) {
                        echo '<span class="text-white bg-red-700 block p-2 rounded-lg mb-2">This account has either been archived or not yet approved by managment. For help, notify <a href="mailto:volunteer@fredspca.org">volunteer@fredspca.org</a>.</span>';
                    }
                    if (isset($_GET['registerSuccess'])) {
                        echo '<span class="text-white text-center bg-green-700 block p-2 rounded-lg mb-2">Registration Successful! Please login below.</span>';
                    }
                    ?>
                    <div class="mb-4">
                        <label class="block text-white font-medium mb-2" for="username">Login</label>
                        <input class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400" type="text" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-white font-medium mb-2" for="password">Password</label>
                        <input class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400" type="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <a href="#" class="text-[#fafafa] text-sm hover:underline">Forgot password?</a>
                        <a href="https://healthygenerations.org/" target="_blank" class="text-[#fafafa] text-sm hover:underline">Healthy Generations Website</a>
                    </div>
                    <button class="cursor-pointer w-full bg-[#45892e] hover:bg-blue-600 text-white font-semibold py-3 rounded-lg transition duration-300">Login</button>
                </form>

            </div>
        </div>
    </div>

</body>

</html>