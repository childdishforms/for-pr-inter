const apiUrl = document.querySelector('.app').dataset.contactsUrl;

let contacts = [];
let selectedId = null;
let selectedContact = null;
let editingId = null;
let busy = false;
let selectionRequest = 0;

const list = document.querySelector('#contact-list');
const form = document.querySelector('#contact-form');
const details = document.querySelector('#contact-details');
const detailsEmpty = document.querySelector('#details-empty');
const search = document.querySelector('#contact-search');
const status = document.querySelector('#app-status');
const addButton = document.querySelector('#add-contact');
const editButton = document.querySelector('#edit-contact');
const deleteButton = document.querySelector('#delete-contact');
const saveButton = form.querySelector('button[type="submit"]');

async function api(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        },
    });
    const data = await response.json().catch(() => null);

    if (!response.ok) {
        throw new Error(data?.error || `Ошибка запроса (${response.status}).`);
    }

    return data;
}

function showStatus(message, isError = false) {
    status.textContent = message;
    status.hidden = message === '';
    status.classList.toggle('is-error', isError);
}

function setBusy(value) {
    busy = value;
    addButton.disabled = value;
    editButton.disabled = value;
    deleteButton.disabled = value;
    saveButton.disabled = value;
}

function renderList() {
    const query = search.value.trim().toLocaleLowerCase('ru');
    const visible = contacts.filter(contact =>
        contact.name.toLocaleLowerCase('ru').includes(query) || contact.phone.includes(query)
    );

    list.replaceChildren();
    document.querySelector('#contact-count').textContent = String(contacts.length);
    document.querySelector('#list-empty').hidden = visible.length !== 0;

    for (const contact of visible) {
        const item = document.createElement('li');
        const button = document.createElement('button');
        const avatar = document.createElement('span');
        const copy = document.createElement('span');
        const name = document.createElement('strong');
        const phone = document.createElement('small');

        button.type = 'button';
        button.className = 'contact-item' + (contact.id === selectedId ? ' is-active' : '');
        button.setAttribute('aria-label', `Открыть контакт ${contact.name}`);
        avatar.className = 'avatar';
        avatar.textContent = contact.name.trim().charAt(0).toLocaleUpperCase('ru');
        copy.className = 'contact-copy';
        name.textContent = contact.name;
        phone.textContent = contact.phone;
        copy.append(name, phone);
        button.append(avatar, copy);
        button.addEventListener('click', () => {
            if (busy) return;
            selectContact(contact.id).catch(error => showStatus(error.message, true));
        });
        item.append(button);
        list.append(item);
    }
}

function renderDetails() {
    if (!form.hidden) return;

    details.hidden = !selectedContact;
    detailsEmpty.hidden = Boolean(selectedContact);

    if (!selectedContact) {
        detailsEmpty.textContent = selectedId === null
            ? 'Выберите контакт из списка или добавьте новый.'
            : 'Загрузка контакта...';
        return;
    }

    document.querySelector('#detail-name').textContent = selectedContact.name;
    document.querySelector('#detail-phone').textContent = selectedContact.phone;
    document.querySelector('#detail-email').textContent = selectedContact.email || '—';
    document.querySelector('#detail-note').textContent = selectedContact.note || '—';
}

function render() {
    renderList();
    renderDetails();
}

async function selectContact(id) {
    const request = ++selectionRequest;
    selectedId = id === null ? null : String(id);
    selectedContact = null;
    form.hidden = true;
    render();

    if (selectedId === null) return;

    try {
        const contact = await api(`${apiUrl}/${encodeURIComponent(selectedId)}`);
        if (request !== selectionRequest) return;

        selectedContact = { ...contact, id: String(contact.id) };
        renderDetails();
    } catch (error) {
        if (request !== selectionRequest) return;

        detailsEmpty.textContent = 'Не удалось загрузить контакт.';
        throw error;
    }
}

async function loadContacts(preferredId = selectedId) {
    const data = await api(apiUrl);
    if (!Array.isArray(data)) throw new Error('Сервер вернул неверный список контактов.');

    contacts = data.map(contact => ({ ...contact, id: String(contact.id) }));
    const id = contacts.find(contact => contact.id === String(preferredId))?.id
        ?? contacts[0]?.id
        ?? null;
    await selectContact(id);
}

function openForm(contact = null) {
    editingId = contact?.id ?? null;
    form.reset();
    form.elements.name.value = contact?.name ?? '';
    form.elements.phone.value = contact?.phone ?? '';
    form.elements.email.value = contact?.email ?? '';
    form.elements.note.value = contact?.note ?? '';
    document.querySelector('#form-caption').textContent = contact ? 'Редактирование' : 'Новый контакт';
    document.querySelector('#form-title').textContent = contact ? 'Изменить контакт' : 'Добавить контакт';
    details.hidden = true;
    detailsEmpty.hidden = true;
    form.hidden = false;
    form.elements.name.focus();
}

addButton.addEventListener('click', () => openForm());
editButton.addEventListener('click', () => {
    if (selectedContact) openForm(selectedContact);
});
deleteButton.addEventListener('click', async () => {
    if (busy || !selectedContact || !window.confirm(`Удалить контакт «${selectedContact.name}»?`)) return;

    setBusy(true);
    showStatus('');

    try {
        await api(`${apiUrl}/${encodeURIComponent(selectedId)}`, { method: 'DELETE' });
        await loadContacts(null);
        showStatus('Контакт удалён.');
    } catch (error) {
        showStatus(error.message, true);
    } finally {
        setBusy(false);
    }
});
document.querySelector('#cancel-form').addEventListener('click', () => {
    form.hidden = true;
    render();
});
search.addEventListener('input', renderList);
form.addEventListener('submit', async event => {
    event.preventDefault();
    if (busy) return;

    const data = {
        name: form.elements.name.value.trim(),
        phone: form.elements.phone.value.trim(),
        email: form.elements.email.value.trim(),
        note: form.elements.note.value.trim(),
    };
    if (!data.name || !data.phone) return;

    setBusy(true);
    showStatus('');

    try {
        const saved = await api(
            editingId === null ? apiUrl : `${apiUrl}/${encodeURIComponent(editingId)}`,
            { method: editingId === null ? 'POST' : 'PATCH', body: JSON.stringify(data) }
        );

        search.value = '';
        form.hidden = true;
        editingId = null;
        await loadContacts(saved.id);
        showStatus('Контакт сохранён.');
    } catch (error) {
        showStatus(error.message, true);
    } finally {
        setBusy(false);
    }
});

showStatus('Загрузка контактов...');
loadContacts()
    .then(() => showStatus(''))
    .catch(error => {
        showStatus(error.message, true);
        details.hidden = true;
        detailsEmpty.hidden = false;
        detailsEmpty.textContent = 'Не удалось загрузить контакты.';
    });
