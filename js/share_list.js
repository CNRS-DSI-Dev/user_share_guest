$(document).ready(function() {
    _.defer(function() {
        FileList.fileActions.actions.all = {
            Download: FileList.fileActions.actions.all.Download
        };
        FileList.reload();
        $('#new').remove();
        $('#upload').remove();
    });
});
