<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/style.css'); ?>">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
</head>
<body>

<div class="login-container">
    <h2>register</h2>
             <?php if (isset($error)): ?>
    <p style="color:white;"><?php echo $error; ?></p>
<?php endif; ?>
    <form action="<?php echo site_url('auth/register_submit'); ?>" method="POST">
        <label><i class="fa-regular fa-circle-user"></i> username</label>
        <input type="text" name="username" placeholder="enter username here"required>

        <label><i class="fa-solid fa-key" ></i> password</label>
        <input type="password" name="password" placeholder="enter password here" required>
        <p class="small-text">
        already have an account?
        <a href="<?php echo site_url('auth/login'); ?>" class="link">login</a>
        </p>
         <button type="submit" class="btn">register</button>
    </form>
</div>


</body>
</html>

