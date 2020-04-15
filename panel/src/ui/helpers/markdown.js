export default {
  email(email, text) {
    if (text && text.length > 0) {
      return `[${text}](mailto:${email})`;
    }

    return `<${email}>`;
  },
  link(url, text) {
    if (text && text.length > 0) {
      return `[${text}](${url})`;
    }

    return `<${url}>`;
  }
};

