<h1>BBA Mastro Tracking And Consignment Fields</h1>

<table class="widefat" cellspacing="0">
    <thead>
        <tr>
            <th width="100" class="manage-column">ID</th>
            <th width="500" class="manage-column">Field Name</th>
            <th width="100" class="manage-column">Type</th>
            <th class="manage-column">Description</th>
            <th class="manage-column">Attributes</th>
        </tr>
    </thead>

    <tbody>
        <?php $fields = get_option('bba_admin_custom_order_fields'); ?>
        <?php if($fields !== false): ?>
            <?php foreach($fields as $key=>$value): ?>
                <tr>
                    <td width="100" class="manage-column"><?= $key ?></td>
                    <td width="500" class="manage-column"><?= $value['label'] ?></td>
                    <td width="100" class="manage-column"><?= $value['type'] ?></td>
                    <td class="manage-column"><?= $value['description'] ?></td>
                    <td class="manage-column">
                        <?php if($value['visible']): ?>
                            <span>Show in My Orders / Emails</span><br/>
                        <?php endif; ?>

                        <?php if($value['listable']): ?>
                            <span>Display in View Orders screen</span><br/>
                        <?php endif; ?>

                        <?php if($value['sortable']): ?>
                            <span>Allow Sorting on View Orders screen</span><br/>
                        <?php endif; ?>

                        <?php if($value['filterable']): ?>
                            <span>Allow Filtering on View Orders screen</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if($fields === false): ?>
            <tr>
                <td colspan="5"></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

