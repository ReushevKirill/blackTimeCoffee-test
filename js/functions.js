// Выключение скролла
const disableScroll = () => {
	const fixBlocks = document?.querySelectorAll('.fixed-block')
	const pagePosition = window.scrollY
	const paddingOffset = `${window.innerWidth - vars.bodyEl.offsetWidth}px`

	vars.htmlEl.style.scrollBehavior = 'none'
	fixBlocks.forEach(el => {
		el.style.paddingRight = paddingOffset
	})
	vars.bodyEl.style.paddingRight = paddingOffset
	vars.bodyEl.classList.add('dis-scroll')
	vars.bodyEl.dataset.position = pagePosition
	vars.bodyEl.style.top = `-${pagePosition}px`
}

// Включение скролла
const enableScroll = () => {
	const fixBlocks = document?.querySelectorAll('.fixed-block')
	const body = document.body
	const pagePosition = parseInt(vars.bodyEl.dataset.position, 10)
	fixBlocks.forEach(el => {
		el.style.paddingRight = '0px'
	})
	vars.bodyEl.style.paddingRight = '0px'

	vars.bodyEl.style.top = 'auto'
	vars.bodyEl.classList.remove('dis-scroll')
	window.scroll({
		top: pagePosition,
		left: 0,
	})
	vars.bodyEl.removeAttribute('data-position')
	vars.htmlEl.style.scrollBehavior = 'smooth'
}

// Удаление класса с элемента 
const removeClass = (element, classEl) => element.classList.remove(classEl)

// Добавление класса на элемент
const addClass = (element, classEl) => element.classList.add(classEl)

// Проверка на содержание класса в элементе 
const containsClass = (element, classEl) => element.classList.contains(classEl)

// Включение скролла с последущей прокруткой к секции при нажатии на элемент меню
const scrollToSectionByClickOnAnchors = () => {
	if (!containsClass(vars.menu, 'menu--active')) return

	removeClass(vars.burger, 'burger--active')
	removeClass(vars.menu, 'menu--active')
	enableScroll()
}

// Отправка формы
async function formSend(e) {
	e.preventDefault()
	let error = formValidate(contactsForm)
	let formData = new FormData(contactsForm)
	const buttonSubmit = contactsForm.querySelector('#contacts_form_submit')

	if (error) {
		alert('Заполните обязательные поля')
	} else {
		contactsForm.classList.add('_sending')
		buttonSubmit.textContent = 'Загрузка'
		let response = await fetch('../mail.php', {
			method: 'POST',
			body: formData,
		})

		if (response.ok) {
			let result = await response.json()
			alert(result.message)
			contactsForm.reset()
			contactsForm.classList.remove('_sending')
			buttonSubmit.textContent = 'Насладиться кофе'
		} else {
			alert('Ошибка отправки формы')
			contactsForm.classList.remove('_sending')
			buttonSubmit.textContent = 'Насладиться кофе'
		}
	}
}

// Валидация формы
function formValidate(form) {
	let error = 0
	let formReq = form.querySelectorAll('._req')
	formReq?.forEach((req, index) => {
		const input = req
		const inputType = input?.getAttribute('data-input-type') || null
		formRemoveError(input)

		if (inputType) {
			if (input.value.length < 16 || input.value === '') {
				formAddError(input)
				error++
			}
		} else {
			if (input.value === '') {
				formAddError(input)
				error++
			}
		}
	})

	return error
}

// Добавление класса ошибки
function formAddError(input) {
	input.parentElement.classList.add('_error')
	input.classList.add('_error')
}

// Удаление класса ошибки
function formRemoveError(input) {
	input.parentElement.classList.remove('_error')
	input.classList.remove('_error')
}