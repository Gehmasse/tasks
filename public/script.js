function token() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content')
}

function addEventListeners() {
    document.querySelectorAll('.checkbox').forEach(elem => {
        elem.addEventListener('click', async () => {
                elem.classList.toggle('checked')

                const url = elem.getAttribute('data-url')
                const complete = elem.classList.contains('checked')

                const res = await fetch(`${url}?${complete ? 'complete=1' : ''}`, {
                    method: 'post', headers: {
                        'X-CSRF-TOKEN': token()
                    }, body: JSON.stringify({complete})
                })

                if (!res.ok) {
                    return toast('Failed Marking Task as Done', 'red', actionHideAfterMs())
                }

                toast(await res.text(), 'green', actionHideAfterMs())
            }
        )
    })

    // document.querySelectorAll('.toggle').forEach(
    //     elem => elem.addEventListener('click', () => {
    //         elem.classList.toggle('checked')
    //     })
    // )
    //
    // document.querySelectorAll('.optional-input input[type="checkbox"]').forEach(elem => {
    //     elem.addEventListener('change', () => {
    //         const input = elem.parentElement.querySelector('.optional input')
    //
    //         if (elem.checked) {
    //             input.removeAttribute('disabled')
    //         } else {
    //             input.setAttribute('disabled', '1')
    //         }
    //     })
    // })
}

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

    action ??= () => {
    }

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

function sync() {
    toast('Syncing ...', 'yellow', async oldToast => {
        while (true) {
            const res = await fetch('/sync')

            if (!res.ok) {
                oldToast.style.display = 'none'
                return toast('Sync Error', 'red', actionHideAfterMs())
            }

            const json = await res.json()

            if (json.finished) {
                oldToast.style.display = 'none'
                return toast('Sync finished', 'green', actionHideAfterMs(10_000))
            }

            toast(json.message, 'yellow', actionHideAfterMs(10_000))
        }
    })
}
