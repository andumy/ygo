const languages = {
    'English': 'EN',
    'French': 'FR',
    'German': 'DE',
    'Italian': 'IT',
    'Portuguese': 'PT',
    'Spanish': 'SP',
    'Japanese': 'JP',
    'Korean': 'KR',
    'S-Chinese': 'SC',
    'T-Chinese': 'TC',
}

const conditions = {
    'MT': 'MINT',
    'NM': 'NEAR MINT',
    'EX': 'EXCELLENT',
    'GD': 'GOOD',
    'LP': 'LIGHT PLAYED',
    'PL': 'PLAYED',
    'PO': 'POOR',
}

const showBoxes = (card,code,order,el,addToCart,isFirstEdition,lang,condition,rarity) => {
    if(card === undefined || code === undefined || order === undefined){
        return;
    }
    const realCard = card.split(' (V.')[0]?.trim();
    const div = document.createElement('div');
    div.style = 'position:absolute; top:0; left:-10px; background-color:white; padding:2px;margin:2px;border-radius:4px; z-index:1000;display:flex;justify-content:center;align-items:center;font-size:10px;';

    // Fix fetch to use HTTP if your local server isn't using HTTPS
    fetch(`http://localhost/card-info?card=${encodeURIComponent(realCard)}`)
        .then((response) => response.json())
        .then((data) => {
            // Safely update the div content with the received data
            switch (data['message']){
                case 'Card not found':
                    div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:black;">?</p>';
                    break;
                case 'Card needed':
                    div.innerHTML = '<p style="pointer-events: none; margin:0;border:0;padding:0;color:green;">✓</p>';
                    el.appendChild(div);
                    break;
            }
        })
        .catch((error) => {
            console.error('Error fetching data:', error);
        });

    div.addEventListener('click', () => {
        fetch(
            `http://localhost/order-card?
            card=${encodeURIComponent(realCard)}
            &code=${encodeURIComponent(code)}
            &order=${encodeURIComponent(order)}
            &rarity=${encodeURIComponent(rarity)}
            &is_first_edition=${encodeURIComponent(isFirstEdition ? '1' : '0')}
            &lang=${encodeURIComponent(languages[lang])}
            &condition=${encodeURIComponent(conditions[condition])}
        `)
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
                        if(addToCart !== false){
                            addToCart.click();
                        }
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
        const cart = el.parentNode.parentNode.parentNode.querySelector('button');

        const card = el.querySelector('a')?.innerText;
        const code = el.parentNode.querySelector('.col-product')?.querySelector('a')?.innerText;
        const isFirstEdition = el.parentNode.querySelector('.col-product')?.querySelector('[aria-label="First Edition"]') !== null;
        const condition = el.parentNode.querySelector('.col-product')?.querySelectorAll('a')[1]?.innerText;
        const lang = el.parentNode.querySelector('.col-product')?.querySelector('.product-attributes>span')?.ariaLabel;
        const rarity = el.parentNode.querySelector('.col-product')?.querySelector('svg')?.ariaLabel;

        showBoxes(card,code,order,el,cart,isFirstEdition,lang,condition,rarity)
    });
}

const handleShoppingCart = () => {
    const order = document.querySelector('.seller-name')?.querySelector('a')?.innerText;

    [...document.querySelectorAll('tr')].map(tr => {
        return {
            "el": tr.querySelectorAll('td')[2],
            "card": tr.querySelectorAll('td')[2]?.querySelector('a')?.innerText,
            "code": tr.querySelectorAll('td')[3]?.querySelector('.row')?.querySelector('a')?.querySelector('span').innerText,
            "isFirstEdition": tr.querySelectorAll('td')[3]?.querySelector('[aria-label="First Edition"]') !== null,
            "condition": tr.querySelectorAll('td')[3]?.querySelectorAll('a')[2]?.innerText,
            "lang": tr.querySelectorAll('td')[3]?.querySelector('.col-icon').querySelector('span>span').ariaLabel,
            "rarity": tr.querySelectorAll('td')[3]?.querySelector('svg')?.ariaLabel,
        }
    }).filter(e => e.card !== undefined).forEach((element) => {
        const el = element.el;
        const card = element.card;
        const code = element.code;
        const isFirstEdition = element.isFirstEdition;
        const lang = element.lang;
        const condition = element.condition;
        const rarity = element.rarity;

        showBoxes(card,code,order,el, false,isFirstEdition,lang,condition,rarity)
    })
}


if(document.querySelector('h1')?.innerText === "Shopping Cart"){
    handleShoppingCart();
} else {
    handleProductPage();
}
