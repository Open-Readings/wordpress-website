(function () {
    'use strict';

    document.addEventListener('click', async function (event) {
        const button = event.target.closest('.or-email-preview-button');
        if (!button) {
            return;
        }
        event.preventDefault();

        const page = elementor.getPanelView().getCurrentPageView();
        const view = page.getOption('editedElementView');
        const settings = view.getEditModel().get('settings');
        const dialog = document.createElement('dialog');
        dialog.className = 'or-email-preview';
        dialog.setAttribute('aria-labelledby', 'or-email-preview-title');

        const header = document.createElement('div');
        header.className = 'or-email-preview-header';
        const title = document.createElement('h2');
        title.id = 'or-email-preview-title';
        title.textContent = orEmailPreview.title;
        const close = document.createElement('button');
        close.type = 'button';
        close.textContent = orEmailPreview.close;
        close.addEventListener('click', function () { dialog.close(); });
        header.append(title, close);

        const subject = document.createElement('p');
        subject.className = 'or-email-preview-subject';
        subject.textContent = settings.get('custom_email_subject') || '';
        const note = document.createElement('p');
        note.textContent = orEmailPreview.note;
        const status = document.createElement('p');
        status.setAttribute('role', 'status');
        status.textContent = orEmailPreview.loading;
        const frame = document.createElement('iframe');
        frame.title = orEmailPreview.title;
        frame.setAttribute('sandbox', '');
        frame.setAttribute('referrerpolicy', 'no-referrer');
        frame.hidden = true;
        dialog.append(header, subject, note, status, frame);
        document.body.append(dialog);

        const request = new AbortController();
        dialog.addEventListener('close', function () {
            request.abort();
            dialog.remove();
            button.focus();
        });
        dialog.showModal();
        close.focus();

        try {
            const response = await fetch(orEmailPreview.url, {
                method: 'POST',
                credentials: 'same-origin',
                signal: request.signal,
                body: new URLSearchParams({
                    action: 'or_preview_email',
                    nonce: orEmailPreview.nonce,
                    post_id: elementor.config.document.id,
                    content: settings.get('email_body') || '',
                }),
            });
            if (!response.ok) {
                throw new Error('Preview request failed');
            }
            const result = await response.json();
            if (!result.success || typeof result.data.html !== 'string') {
                throw new Error('Invalid preview response');
            }
            frame.srcdoc = result.data.html;
            frame.hidden = false;
            status.remove();
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = orEmailPreview.error;
            }
        }
    });
}());
