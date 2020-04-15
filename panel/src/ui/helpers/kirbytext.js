export default {
  email(email, text) {
    if (text && text.length > 0) {
      return `(email: ${email} text: ${text})`;
    }

    return `(email: ${email})`;
  },
  link(url, text) {
    if (text && text.length > 0) {
      return `(link: ${url} text: ${text})`;
    }

    return `(link: ${url})`;
  }
};
