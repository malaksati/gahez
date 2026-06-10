export function buildDeleteConfirmMessage({
    name = '',
    confirmText = 'Delete this item?',
    cannotUndoText = 'This action cannot be undone.',
    extraText = '',
}) {
    let message = confirmText;

    if (name) {
        message += `\n\n"${name}"`;
    }

    if (extraText) {
        message += `\n\n${extraText}`;
    }

    message += `\n\n${cannotUndoText}`;

    return message;
}

export function confirmDelete({
    name = '',
    labels = {},
    extraText = '',
    fallbackConfirm = 'Delete this item?',
    fallbackCannotUndo = 'This action cannot be undone.',
}) {
    const message = buildDeleteConfirmMessage({
        name,
        confirmText: labels.confirmDelete ?? fallbackConfirm,
        cannotUndoText: labels.cannotUndo ?? fallbackCannotUndo,
        extraText,
    });

    return window.confirm(message);
}
