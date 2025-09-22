const showSetSync = () => {
    document.querySelectorAll('table.card-list').forEach(table => {

        let output = ''
        table.querySelectorAll('tbody>tr').forEach(node => {
            const card_set_code = node.querySelector('td:nth-child(1)>a')?.innerText.trim();
            const card_name = node.querySelector('td:nth-child(2)>a')?.innerText.trim().replaceAll('"', '\\"');
            const rarityEntries = node.querySelectorAll('td:nth-child(3)>a');
            if(rarityEntries){
                rarityEntries.forEach( rarityNode => {
                    const rarity = rarityNode.innerText.trim();
                    output += `{"code": "${card_set_code}", "name": "${card_name}", "rarity": "${rarity}"},`
                })
            }
        });

        console.log(`[${output.slice(0, -1)}]`)
    });

}

showSetSync();
