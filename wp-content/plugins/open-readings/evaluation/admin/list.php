<div id=displayContainer>




    <?php
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM wp_or_registration AS r LEFT JOIN wp_or_registration_evaluation AS e ON r.hash_id = e.evaluation_hash_id", ARRAY_A);
    $status = [0, 0, 0, 0, 0];
    foreach ($results as $db_result) {
        $status[$db_result['status']]++;
    }
    echo '
<form class="abstract-half-div" id="statusForm" action="evaluation" method="post">
<label for="selectOption">Select an option:</label>
<select id="selectOption" name="selectedOption">
    <option value="all">All</option>
    <option value="option2">Option 2</option>
    <option value="option3">Option 3</option>
</select>
<input type="hidden" name="action" value="evaluation">
<input type="hidden" name="function" value="fetch_data">
<input type="submit" value="Submit">
<p> Not checked: ' . $status[0] . ', Accepted: ' . $status[1] . ', Waiting for update: ' . $status[2] + $status[4] . ', Rejected: ' . $status[3] . '</p>
</form>
<div id="resultContainer">
';

    echo '<table border="1">';
    echo '<tr><th>Status</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Research area</th><th>Presentation type</th><th>Hash ID</th></tr>';
    // Process the fetched data
    foreach ($results as $result) {
        echo '<tr>';
        echo '<td>' . $result['status'] . '</td>';
        echo '<td>' . $result['first_name'] . '</td>';
        echo '<td>' . $result['last_name'] . '</td>';
        echo '<td>' . $result['email'] . '</td>';
        echo '<td>' . $result['research_area'] . '</td>';
        echo '<td>' . $result['presentation_type'] . '</td>';
        echo '<td>' . $result['hash_id'] . '</td>';
        echo '</tr>';
    }
    echo '
</table>
</div>
';

    ?>
</div>