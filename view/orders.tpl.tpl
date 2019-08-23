<tr style="background-color: [+color+] !important">
    <td>[+id+]</td>
    <td>[+create_ad+]</td>
    <td>[+items+]</td>
    <td>[+sum.total+]</td>
    <td>[+customer.name+], [+customer.phone+], [+customer.email+], [+customer.city+], [+customer.address+]</td>
    <td>[+delivery+]<br>[+payment+]</td>
    <td class="align-middle">[+status.select+]</td>
    <td class="align-middle">
        <div class="actions text-center text-nowrap">
            <a href="[+mod_page+]&action=orders/delete&orderID=[+id+]" onclick="return confirm('[+lang.confirm.delete.order+]')">
                <i class="fa fa-trash text-danger"></i>
            </a>
        </div>
    </td>
</tr>