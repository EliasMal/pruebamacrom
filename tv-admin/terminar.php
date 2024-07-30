<?php
    session_name("loginUsuario");
    session_start();

    // Connect to the database
    $conn = mysqli_connect('macromautopartes.com', 'u619477378_root','jSJLK6AqN%fwUOskf5@R','u619477378_macromau');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "UPDATE Usuarios SET OnlineNow = 0 where _id = '{$_SESSION["_id"]}' and Username = '{$_SESSION["usr"]}'";

    if (mysqli_query($conn, $sql)) {
    } else {
        echo "Error inserting data: " . mysqli_error($conn);
    }

    // Close the connection
    mysqli_close($conn);

    session_destroy();

    header("Location: index.php");

