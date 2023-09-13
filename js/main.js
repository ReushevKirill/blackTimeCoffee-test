const menuLinks = document.querySelectorAll('[data-menu-link]')
const contactsForm = document.querySelector('#contacts_form')
const contactPhone = document.querySelector('#contacts_input_tel')

menuLinks.forEach(link =>
	link.addEventListener('click', scrollToSectionByClickOnAnchors)
)

const contactPhoneMask = IMask(contactPhone, {
	mask: '+ (00) 000-00-00',
})

contactPhone.addEventListener('click', captchaLoad)

contactsForm.addEventListener('submit', formSend)