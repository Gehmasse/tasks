function actionHideAfterMs(time = 3000) {
    return toast => setTimeout(() => {
        toast.style.display = 'none'
    }, time)
}


function toast(message, color, action) {
    const elem = document.createElement('div')
    elem.classList.add('toast')
    elem.classList.add(color)
    elem.innerText = message

    document.querySelector('footer').prepend(elem)

    action ??= actionHideAfterMs(5_000)

    action(elem)
}

function checkConnectionToast(href) {
    toast('Check connection for ' + href, 'yellow', async t => {
        const res = await fetch(href)

        t.style.display = 'none'

        if (res.ok) {
            toast('Connection ok', 'green', actionHideAfterMs())
        } else {
            toast('Connection failed', 'red', actionHideAfterMs())
        }
    })
}
