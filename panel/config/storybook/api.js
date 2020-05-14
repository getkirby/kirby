const request = {
  delete(path) {
    console.log('DELETE ' + path);
  },
  get(path) {
    console.log('GET ' + path);
    return {};
  },
  patch(path) {
    console.log('PATCH ' + path);
  },
  post(path) {
    console.log('POST ' + path);
  }
};

export default {
  ...request,
  files: {
    changeName(parent, filename, name) {
      request.patch(parent + "/files/" + filename + "/name");
      return {
        filename: filename,
        name: name,
        parent: parent,
      };
    },
    delete(parent, filename) {
      return request.delete(parent + "/files/" + filename);
    },
    get(parent, filename) {
      request.get(parent + "/files/" + filename);
      return {
        extension: "jpg",
        filename: "test.jpg",
        name: "test",
      };
    },
  },
  languages: {
    create(language) {
      return request.post("languages", language);
    },
    delete(code) {
      return request.delete("languages/" + code);
    },
    get(code) {
      request.get("languages/" + code);
      return {
        code: code,
        name: "English",
        locale: "en_US",
        direction: "ltr",
      };
    },
    update(code, values) {
      return request.patch("languages/" + code, values);
    },
  },
  pages: {
    delete(id) {
      return request.delete("pages/" + id);
    },
    duplicate(id, slug, params) {

    },
    get(id) {
      request.get("pages/" + id);
      return {
        blueprints: [
          { name: "article", title: "Article" },
          { name: "project", title: "Project" }
        ],
        hasChildren: true,
        hasFiles: true,
        slug: "photography",
        template: "article",
        title: "Photography",
      };
    },
    changeTemplate(id, template) {
      request.patch("pages/" + id + "/template");
      return {
        id: id,
        template: template,
      };
    },
    changeTitle(id, title) {
      request.patch("pages/" + id + "/title");
      return {
        id: id,
        title: title,
      };
    },
    create(parent, values) {
      request.post("pages/" + parent + "/children", values);
      return values;
    }
  },
  site: {
    get(id) {
      request.get("site");
      return {
        title: "Maegazine",
      };
    },
    changeTitle(title) {
      request.patch("site/title");
      return {
        title: title,
      };
    }
  },
  system: {
    register() {
      return request.post("system/register");
    }
  },
  users: {
    changeEmail(id, email) {
      request.patch("users/" + id + "/email");
      return {
        id: id,
        email: email,
      };
    },
    changeLanguage(id, code) {
      request.patch("users/" + id + "/language");
      return {
        id: id,
        language: code,
      };
    },
    changeName(id, name) {
      request.patch("users/" + id + "/name");
      return {
        id: id,
        name: name,
      };
    },
    changePassword(id, password) {
      request.patch("users/" + id + "/password");
      return {
        id: id
      };
    },
    changeRole(id, role) {
      request.patch("users/" + id + "/role");
      return {
        id: id,
        role: role
      };
    },
    delete(id) {
      return request.delete("users/" + id);
    },
    get(id) {
      request.get("users/" + id);
      return {
        role: "admin",
        email: "ada@example.com",
        name: "Ada Lovelace",
        language: "en"
      }
    }
  },
};

