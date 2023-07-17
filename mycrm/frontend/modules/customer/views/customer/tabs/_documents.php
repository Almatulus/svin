<?php
?>
<div class="row">
    <div class="col-sm-12">
        <table class="table table-bordered">
        <thead>
            <tr>
                <th>Сформирован</th>
                <th>Сформировал</th>
                <th>Тип документа</th>
                <th>Ссылка</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $key => $document) {
                echo "<tr>" .
                    "<td>{$document->date}</td>" .
                    "<td>{$document->user->fullName}</td>" .
                    "<td>{$document->template->name}</td>" .
                    "<td><a href=\"{$document->path}\">Открыть</a></td>" .
                 "</tr>";
            } ?>
        </tbody>
        </table>
    </div>
</div>