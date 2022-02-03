<div class="wrap">
    <h1>SWF plugin updates</h1>
    <p>Vul hieronder de authenticatie sleutel in om updates te (kunnen) ontvangen.</p>

    <form action="<?= admin_url('tools.php?page=swf-plugin-updates'); ?>" method="POST">
        <fieldset>
            <p>
                <label for="updater_auth_key">
                    Updater authenticatie sleutel
                </label>

                <input type="password" name="updater_auth_key" value="<?= $authKey; ?>" id="updater_auth_key">
            </p>

            <p class="submit">
                <input type="submit" name="swf_updater_save" id="swf_updater_save" class="button button-primary" value="Gegevens opslaan">
            </p>
        </fieldset>
    </form>
</div>
