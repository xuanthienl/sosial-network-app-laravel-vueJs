<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        .button {
            background-color: #8FBC8F; /* Green */
            border: none;
            color: white;
            padding: 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 10px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <h5>Hi {{ $username }} !</h5>
    <p>Your password has been changed to:</p>
    <p>New password: {{ $password }}</p>
    <p>Thank you !</p>
</body>
</html>