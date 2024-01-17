<?php

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the form data
    $subject = $_POST["subject"];
    $recipients = $_POST["recipients"];
    $attachmentName = $_POST["attachment_name"];
    $attachmentList = $_POST["attachment_list"];
    $replaceWith = $_POST["replace_with"];
    $message = $_POST["message"];
    $attachment = $_POST["attachment"];
    $useTemplate = isset($_POST["use_template"]) ? $_POST["use_template"] : "No"; // Default to "No" if not set

    // TODO: Add code to send the email with the provided information
    // This is just a placeholder; you may use a library like PHPMailer or implement your own logic.
    // Example: sendEmail($subject, $recipients, $attachmentName, $attachmentList, $replaceWith, $message, $attachment, $useTemplate);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailer Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input,
        textarea,
        button,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h2>Mailer Admin</h2>

        <label for="subject">Subject:</label>
        <input type="text" name="subject" required>

        <label for="recipients">Recipients:</label>
        <input type="text" name="recipients" required>

        <label for="attachment_name">Attachment Name:</label>
        <input type="text" name="attachment_name">

        <label for="attachment_list">Attachment List:</label>
        <input type="text" name="attachment_list">

        <label for="replace_with">Replace With:</label>
        <input type="text" name="replace_with">

        <label for="message">Message:</label>
        <textarea name="message" required></textarea>

        <label for="attachment">Attachment:</label>
        <input type="file" name="attachment">

        <label for="use_template">Use Template?</label>
        <input type="checkbox" name="use_template" value="Yes">


        <button type="submit">Send</button>
    </form>

</body>

</html>