<h1>Excluded Product Categories</h1>

<form action="<?php echo esc_url(admin_url('admin-post.php')) ?>" method="post">
    <input type="hidden" name="action" value="update_excluded_categories">
    <table class="widefat" cellspacing="0">
        <thead>
            <tr>
                <th width="10" class="manage-column">
                    <input id="all-checkbox" type="checkbox">&nbsp; Select All
                </th>
                <th width="10" class="manage-column">ID</th>
                <th width="10" class="manage-column">Category</th>
                <th width="10" class="manage-column">Description</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $excluded_cat = explode(',', BBAM_Utils::getConfig('excluded_categories'));
            $categories = get_terms('product_cat', array(
                'orderedby' => 'name',
                'order' => 'asc',
                'hide_empty' => false
            ));

            if (!$categories) {
                echo '<tr><td rowspan="4">No Data Available</td></tr>';
            }

            foreach ($categories as $key => $cat) {
                echo '<tr>';
                if (!in_array($cat->name, $excluded_cat)) {
                    echo "<td width='10' class='manage-column'>
                            <input type='checkbox' name='excluded_cat[]'  value='$cat->name'/>
                        </td>";
                } else {
                    echo "<td width='10' class='manage-column'>
                            <input type='checkbox' name='excluded_cat[]'  value='$cat->name' checked/>
                        </td>";
                }
                echo '<td width="10" class="manage-column">' . $cat->term_id . '</td>';
                echo '<td width="10" class="manage-column">' . $cat->name . '</td>';
                echo '<td width="10" class="manage-column">' . $cat->description . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

    <div class="form-action-container">
        <button type="submit" class="button-primary woocommerce-save-button">
            Save Changes
        </button>
    </div>
</form>

<style>
    form {
        padding-right: 20px;
    }
    th {
        font-weight: bold !important;
    }
    td input[type=checkbox] {
        margin-left: 8px;  
    }
    .form-action-container {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
    }
    .button-primary {
        margin-top: 20px !important;
    }
</style>

<script>
    jQuery(document).ready(() => {
        jQuery('#all-checkbox').click(()=> {
            const is_checked = document.getElementById('all-checkbox').checked;
            if (is_checked) {
                const checkboxes = jQuery('td input[type=checkbox]');
                checkboxes.each((index) => {
                    jQuery(checkboxes[index]).prop('checked', true);
                });
                return;
            }

            const checkboxes = jQuery('td input[type=checkbox]:checked');
            checkboxes.each(index => {
                jQuery(checkboxes[index]).prop('checked', false);
            });
        });
    });
</script>