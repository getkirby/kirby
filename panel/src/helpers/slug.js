import slugify from "slugify";

export default text => {
  return slugify(text, {
    remove: /[$*_+~.,;:()'"`!?§$%/=#@]/g
  }).toLowerCase();
};
