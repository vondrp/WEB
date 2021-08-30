<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
    MVC is working - pages index
    <br>
    <?php
        foreach($data['users'] as $user){
            echo "Information: ". $user->username ." ". $user->email;
            echo "<br>";
        }
    ?>

</body>
</html>