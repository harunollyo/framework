window.addEventListener('DOMContentLoaded', async function () {
    const checkUrl = frameworkExample.check_url;
    const fileUrl = frameworkExample.file_url;

    try {
        const fileResponse = await fetch(fileUrl);

        if (!fileResponse.ok) {
            console.error('Failed to load file:', fileResponse.status, fileResponse.statusText);
            return;
        }

        const blob = await fileResponse.blob();
        const file = new File([blob], 'file.txt', { type: blob.type || 'text/plain' });
        const formData = new FormData();
        formData.append('attachment', file);

        const response = await fetch(checkUrl, {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            console.error('Upload failed:', response.status, response.statusText);
            return;
        }

        const data = await response.json();
        console.log(data);
    } catch (error) {
        console.error('Error uploading file:', error);
    }
});
