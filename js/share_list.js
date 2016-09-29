$(document).ready(function() {
    _.defer(function() {
        FileList.fileActions.actions.all = {
            Download: FileList.fileActions.actions.all.Download
        };
        FileList.reload();
        $('a.delete-selected').remove();
    });
});
