export default {
  inserted: el => {
    el.addEventListener("keyup", e => {
      if (e.keyCode === 9) {
        el.dataset.tabbed = true;
      }
    });
    el.addEventListener("blur", () => {
      delete el.dataset.tabbed;
    });
  }
};
