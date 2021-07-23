
export default {
  install(app) {
    app.directive('direction', {
      inserted(el, binding, vnode) {
        if (vnode.context.disabled !== true) {
          el.dir = vnode.context.$direction;
        } else {
          el.dir = null;
        }
      }
    });
  }
}