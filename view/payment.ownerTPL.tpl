<table class="table data nowrap">
    <thead>
    <tr>
        <td style="width: 1%">ID</td>
        <td>[+lang.title+]</td>
        <td style="width: 50px">[+lang.rank+]</td>
        <td style="width: 1%"></td>
    </tr>
    </thead>
    <tbody>
    <form action="[+mod_page+]&action=payment/add" method="post">
        <tr>
            <td></td>
            <td>
                <input type="text" name="new_title" class="form-control form-control-sm">
            </td>
            <td>
                <input type="text" name="new_rank" class="form-control form-control-sm">
            </td>
            <td>
                <button type="submit" class="btn btn-sm btn-success">[+lang.add+]</button>
            </td>
        </tr>
    </form>
    [+dl.wrap+]
    </tbody>
</table>
<div class="paginate">
    [+pages+]
</div>