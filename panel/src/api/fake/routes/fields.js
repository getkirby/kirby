// TODO: Don't rely on storybook mock data
import {
  File,
  Files,
  Page,
  Pages,
  User,
  Users
} from "../../../../storybook/data/PickerItems.js";

export default (api) => {

  // temp for models fields, dialogs, pickers
  // TODO: figure out actual endpoint
  const toItems = (request, model) => {
    return JSON.parse(request.queryParams.ids).map(id => model(id));
  };

  const toOptions = (request, models) => {
    return models(
      parseInt(request.queryParams.page),
      parseInt(request.queryParams.limit),
      request.queryParams.parent,
      request.queryParams.search
    );
  };

  api.get("/field/files/items", (schema, request) => {
    return toItems(request, File);
  });

  api.get("/field/files/options", (schema, request) => {
    return toOptions(request, Files);
  });

  api.get("/field/pages/items", (schema, request) => {
    return toItems(request, Page);
  });

  api.get("/field/pages/options", (schema, request) => {
    return toOptions(request, Pages);
  });

  api.get("/field/users/items", (schema, request) => {
    return toItems(request, User);
  });

  api.get("/field/users/options", (schema, request) => {
    return toOptions(request, Users);
  });

};
