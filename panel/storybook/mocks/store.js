
export default {
  state: {
    content: {},
    languages: {}
  },
  dispatch(action, ...args) {
    console.log(`store.dispatch(${action}, ${args[0]})`);
  },
  commit() {
    console.log(`store.commit(${action}, ${args[0]})`);
  }
};
