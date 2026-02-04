function toggleSidebar() {
	const sidebar = document.getElementById('sidebar')
	const texts = sidebar.querySelectorAll('.sidebar-text')

	if (sidebar.classList.contains('w-64')) {
		sidebar.classList.remove('w-64')
		sidebar.classList.add('w-16')
		texts.forEach(t => t.classList.add('hidden'))
	} else {
		sidebar.classList.remove('w-16')
		sidebar.classList.add('w-64')
		texts.forEach(t => t.classList.remove('hidden'))
	}
	if (sidebar.classList.contains('w-16')) {
	  	document.querySelectorAll('.sidebar-submenu').forEach(sm => {
	    	sm.classList.add('hidden')
	  	})
	}
}
function toggleSidebarMobile() {
  	const sidebar = document.getElementById('sidebar')
  	sidebar.classList.toggle('hidden')
}
function toggleGroup(button) {
	const group = button.closest('.sidebar-group')
	const submenu = group.querySelector('.sidebar-submenu')
	const icon = button.querySelector('[class*="expand"]')

	submenu.classList.toggle('hidden')

  	if (icon) {
    	icon.classList.toggle('rotate-180')
  	}
}
(function () {
  const currentPath = window.location.pathname.replace(/\/$/, '')

  const links = document.querySelectorAll('.sidebar-submenu a')

  links.forEach(link => {
    const linkPath = new URL(link.href).pathname.replace(/\/$/, '')

    if (linkPath === currentPath) {
      // 1. Marcar link activo
      link.classList.add('sidebar-link-active')

      // 2. Abrir submenu
      const submenu = link.closest('.sidebar-submenu')
      submenu.classList.remove('hidden')

      // 3. Girar icono del grupo
      const group = link.closest('.sidebar-group')
      const toggleIcon = group.querySelector('[class*="expand"]')
      if (toggleIcon) {
        toggleIcon.classList.add('rotate-180')
      }
    }
  })
})()