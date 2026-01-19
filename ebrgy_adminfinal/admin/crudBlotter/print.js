function printBlotter(blotterId) {
    if (!blotterId) {
        alert("Invalid blotter ID.");
        return;
    }
    window.open(`crudBlotter/print.php?blotter_id=${blotterId}`, '_blank', 'width=800,height=600');
}
