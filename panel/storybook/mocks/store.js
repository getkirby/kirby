import Store from "@/app/store/store.js";

Store.subscribe(mutation => {
  console.log(`store.commit(${mutation.type}, ${mutation.payload})`);
})

Store.subscribeAction(action => {
  console.log(`store.dispatch(${action.type}, ${action.payload})`);
});

export default Store;
