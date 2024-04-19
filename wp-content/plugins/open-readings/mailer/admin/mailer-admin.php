<?php





if (isset($_POST['send_emails'])) {
    SendEmails();
}






function fetch_registered_emails()
{

    global $wpdb;
    $table_name = $wpdb->prefix . get_option('or_registration_database_table');
    ;
    $query_result = $wpdb->get_results("SELECT DISTINCT email FROM $table_name");
    $emails = array();
    foreach ($query_result as $row) {
        array_push($emails, $row->email);
    }
    return $emails;



}




// Handle form submission
function SendEmails()
{
    // Process the form data
    $subject = $_POST["subject"];
    $recipients = $_POST["recipients"];
    $message = $_POST["message"];

    // transform the string to remove any \ characters
    $useTemplate = isset($_POST["use_template"]) ? $_POST["use_template"] : "No"; // Default to "No" if not set

    // Extracted from the provided code snippets
    $all_recipients = explode('\n', $_POST['recipients']);  // Replace with your actual API key

    global $or_mailer;
    global $wpdb;


    $sent_counter = 0;

    if (!empty($_POST['send_to_registered']) && $_POST['send_to_registered'] == 'Yes') {
        $registered_emails = fetch_registered_emails();
        $all_recipients = array_merge($all_recipients, $registered_emails);
    }

    foreach ($all_recipients as $email) {
        $email = trim($email);
        $query_result = $wpdb->query("SELECT * FROM wp_or_mailer WHERE mail = '$email'");
        if ($query_result == 0) {

            $wpdb->insert('wp_or_mailer', array('mail' => $email));
        }
        $email_was_sent = $wpdb->get_results("SELECT is_sent FROM wp_or_mailer WHERE mail = '$email'");
        if ($email_was_sent[0]->is_sent == 0) {
            $result = $or_mailer->send_OR_mail($email, $subject, stripslashes($message));
            if ($result) {
                $wpdb->update('wp_or_mailer', array('is_sent' => 1), array('mail' => $email));
                $sent_counter++;
            } else {
                $wpdb->update('wp_or_mailer', array('is_sent' => 0), array('mail' => $email));
            }
        }






    }

    echo '<script>alert("Sent ' . $sent_counter . ' emails out of ' . count($all_recipients) . '")</script>';

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

    <form method="post">
        <h2>Mailer Admin</h2>

        <label for="subject">Subject:</label>
        <input type="text" name="subject" required>

        <label for="recipients">Recipients:</label>
        <textarea type="text" name="recipients" required></textarea>

        <label for="message">Message:</label>
        <textarea name="message" required></textarea>

        <label for="use_template">Use Template?</label>
        <input type="checkbox" name="use_template" value="Yes">

        <label for="send_to_registered">Send to all registered?</label>
        <input type="checkbox" name="send_to_registered" value="Yes">

        <button type="submit" name="send_emails">Send</button>
    </form>

</body>

</html>