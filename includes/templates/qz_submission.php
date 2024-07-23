<?php
global $wpdb;
$tablename = PLUGIN_PREFIX . '_entries';
$query = "SELECT * FROM $tablename";
$result = $wpdb->get_results($query);
$result = array_reverse($result);
?>
<div class="container-fluid">
    <div class="mt-4">
        <h3>Quiz Entries</h3>
    </div>
    <div class="mt-4">
        <table id="quiz-entries" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Body Part</th>
                    <th>Date</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = 1;
                foreach ($result as $key => $value) :
                    $data = maybe_unserialize($value->data);
                    $date = new DateTime($value->created_at);
                    $formattedDate = $date->format('Y-m-d');
                    echo "<tr>";
                    echo "<td>" . $count++ . "</td>";
                    echo "<td><a href='javascript:void(0)' class='view-detail sub_user_name' data-bs-toggle='modal' data-bs-target='#quizDetail'>" . $value->firstname . " " . $value->lastname . "</a></td>";
                    echo "<td>" . $value->email . "</td>";
                    echo "<td>" . $value->phone . "</td>";
                    echo "<td>" . $value->body_part . "</td>";
                    echo "<td>" . $formattedDate . "</td>";
                    echo "<td><a href='javascript:void(0)' class='view-detail' data-bs-toggle='modal' data-bs-target='#quizDetail'><i class='fa fa-external-link'></i><input type='hidden' class='quiz_data' value='" . json_encode($data) . "'/></a></td>";
                    echo "</tr>";
                endforeach;
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Body Part</th>
                    <th>Date</th>
                    <th>View</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="quizDetail" tabindex="-1" aria-labelledby="quizDetailLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quizDetailLabel">Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>