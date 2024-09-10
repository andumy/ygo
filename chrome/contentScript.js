const showBoxes = (card,code,order,el) => {
    if(card === undefined || code === undefined || order === undefined){
        return;
    }
    const realCard = card.split('(')[0]?.trim();
    const div = document.createElement('div');
    div.style = 'position:absolute; top:0; left:-10px; background-color:white; padding:2px;margin:2px;border-radius:4px; z-index:1000;display:flex;justify-content:center;align-items:center;font-size:10px;';

    // Fix fetch to use HTTP if your local server isn't using HTTPS
    fetch(`http://localhost/card-info?card=${encodeURIComponent(realCard)}&code=${encodeURIComponent(code)}`)
        .then((response) => response.json())
        .then((data) => {
            // Safely update the div content with the received data
            switch (data['message']){
                case 'Card not found':
                    div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:black;">?</p>';
                    break;
                case 'Card owned':
                    div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:red;">X</p>';
                    break;
                case 'Card needed':
                    div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:green;">✓</p>';
                    break;
            }
            el.appendChild(div);
        })
        .catch((error) => {
            console.error('Error fetching data:', error);
        });

    div.addEventListener('click', () => {
        fetch(`http://localhost/order-card?card=${encodeURIComponent(realCard)}&code=${encodeURIComponent(code)}&order=${encodeURIComponent(order)}`)
            .then((response) => response.json())
            .then((data) => {
                // Safely update the div content with the received data
                switch (data['message']){
                    case 'Multiple instances found':
                        div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:black;">multiple found</p>';
                        break;
                    case 'Not Found':
                        div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:black;">not found</p>';
                        break;
                    case 'Manual addition required':
                        div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:black;">manual required</p>';
                        break;
                    case 'Order not Found':
                        div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:black;">order not found</p>';
                        break;
                    case 'Added':
                        div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:green;">Added</p>';
                        break;
                }
            })
            .catch((error) => {
                console.error('Error fetching data:', error);
            });
    })

    // Ensure the parent element is positioned relatively to position the div correctly
    el.style.position = 'relative';
}

const handleProductPage = () => {
    const order = document.querySelector('.page-title-container')?.querySelector('h1')?.innerText?.split('\n')[0];
    document.querySelectorAll('.col-seller').forEach((el) => {
        const card = el.querySelector('a')?.innerText;
        const code = el.parentNode.querySelector('.col-product')?.querySelector('a')?.innerText;
        showBoxes(card,code,order,el)
    });
}

const handleShoppingCart = () => {
    const order = document.querySelector('.seller-name')?.querySelector('a')?.innerText;

    [...document.querySelectorAll('tr')].map(tr => {
        return {
            "el": tr.querySelectorAll('td')[2],
            "card": tr.querySelectorAll('td')[2]?.querySelector('a')?.innerText,
            "code": tr.querySelectorAll('td')[3]?.querySelector('.row')?.querySelector('a')?.querySelector('span').innerText
        }
    }).filter(e => e.card !== undefined).forEach((element) => {
        const el = element.el;
        const card = element.card;
        const code = element.code;

        showBoxes(card,code,order,el)
    })
}


if(document.querySelector('h1').innerText === "Shopping Cart"){
    handleShoppingCart();
} else {
    handleProductPage();
}
