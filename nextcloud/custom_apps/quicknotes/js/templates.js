(function() {
  var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};
templates['attachts'] = template({"1":function(container,depth0,helpers,partials,data,blockParams,depths) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "		<div class='note-attach-grid'>\n			<a target=\"_blank\" href=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"redirect_url") || (depth0 != null ? lookupProperty(depth0,"redirect_url") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"redirect_url","hash":{},"data":data,"loc":{"start":{"line":4,"column":28},"end":{"line":4,"column":44}}}) : helper)))
    + "\">\n				<div class=\"attach-preview note-attach\" attach-file-id=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"file_id") || (depth0 != null ? lookupProperty(depth0,"file_id") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"file_id","hash":{},"data":data,"loc":{"start":{"line":5,"column":60},"end":{"line":5,"column":71}}}) : helper)))
    + "\" data-background-image=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"preview_url") || (depth0 != null ? lookupProperty(depth0,"preview_url") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"preview_url","hash":{},"data":data,"loc":{"start":{"line":5,"column":96},"end":{"line":5,"column":111}}}) : helper)))
    + "\"></div>\n			</a>\n"
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,(depths[1] != null ? lookupProperty(depths[1],"can_delete") : depths[1]),{"name":"if","hash":{},"fn":container.program(2, data, 0, blockParams, depths),"inverse":container.noop,"data":data,"loc":{"start":{"line":7,"column":3},"end":{"line":9,"column":10}}})) != null ? stack1 : "")
    + "		</div>\n";
},"2":function(container,depth0,helpers,partials,data) {
    var lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "				<div class=\"attach-remove original-icon-delete-dark\" title=\""
    + container.escapeExpression((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||container.hooks.helperMissing).call(depth0 != null ? depth0 : (container.nullContext || {}),"quicknotes","Delete attachment",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":8,"column":64},"end":{"line":8,"column":102}}}))
    + "\"></div>\n";
},"compiler":[8,">= 4.3.0"],"main":function(container,depth0,helpers,partials,data,blockParams,depths) {
    var stack1, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "<div class='note-attachts'>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? lookupProperty(depth0,"attachments") : depth0),{"name":"each","hash":{},"fn":container.program(1, data, 0, blockParams, depths),"inverse":container.noop,"data":data,"loc":{"start":{"line":2,"column":1},"end":{"line":11,"column":10}}})) != null ? stack1 : "")
    + "</div>";
},"useData":true,"useDepths":true});
templates['navigation'] = template({"1":function(container,depth0,helpers,partials,data) {
    var helper, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "				<button class=\"circle-toolbar\" style=\"background-color: "
    + container.escapeExpression(((helper = (helper = lookupProperty(helpers,"color") || (depth0 != null ? lookupProperty(depth0,"color") : depth0)) != null ? helper : container.hooks.helperMissing),(typeof helper === "function" ? helper.call(depth0 != null ? depth0 : (container.nullContext || {}),{"name":"color","hash":{},"data":data,"loc":{"start":{"line":30,"column":60},"end":{"line":30,"column":69}}}) : helper)))
    + " \"></button>\n";
},"3":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "			<li class=\"nav-tag with-menu "
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,(depth0 != null ? lookupProperty(depth0,"active") : depth0),{"name":"if","hash":{},"fn":container.program(4, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":40,"column":32},"end":{"line":40,"column":59}}})) != null ? stack1 : "")
    + "\" tag-id=\""
    + container.escapeExpression(((helper = (helper = lookupProperty(helpers,"id") || (depth0 != null ? lookupProperty(depth0,"id") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"id","hash":{},"data":data,"loc":{"start":{"line":40,"column":69},"end":{"line":40,"column":77}}}) : helper)))
    + "\">\n				<a href=\"#\">"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"name") || (depth0 != null ? lookupProperty(depth0,"name") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"name","hash":{},"data":data,"loc":{"start":{"line":41,"column":16},"end":{"line":41,"column":28}}}) : helper))) != null ? stack1 : "")
    + "</a>\n			</li>\n";
},"4":function(container,depth0,helpers,partials,data) {
    return "active";
},"6":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "			<li class=\"nav-note with-menu "
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,(depth0 != null ? lookupProperty(depth0,"active") : depth0),{"name":"if","hash":{},"fn":container.program(4, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":51,"column":33},"end":{"line":51,"column":60}}})) != null ? stack1 : "")
    + "\"  data-id=\""
    + container.escapeExpression(((helper = (helper = lookupProperty(helpers,"id") || (depth0 != null ? lookupProperty(depth0,"id") : depth0)) != null ? helper : container.hooks.helperMissing),(typeof helper === "function" ? helper.call(alias1,{"name":"id","hash":{},"data":data,"loc":{"start":{"line":51,"column":72},"end":{"line":51,"column":80}}}) : helper)))
    + "\">\n"
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,(depth0 != null ? lookupProperty(depth0,"title") : depth0),{"name":"if","hash":{},"fn":container.program(7, data, 0),"inverse":container.program(9, data, 0),"data":data,"loc":{"start":{"line":52,"column":4},"end":{"line":56,"column":11}}})) != null ? stack1 : "")
    + "			</li>\n";
},"7":function(container,depth0,helpers,partials,data) {
    var stack1, helper, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "					<a href=\"#\">"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"title") || (depth0 != null ? lookupProperty(depth0,"title") : depth0)) != null ? helper : container.hooks.helperMissing),(typeof helper === "function" ? helper.call(depth0 != null ? depth0 : (container.nullContext || {}),{"name":"title","hash":{},"data":data,"loc":{"start":{"line":53,"column":17},"end":{"line":53,"column":30}}}) : helper))) != null ? stack1 : "")
    + "</a>\n";
},"9":function(container,depth0,helpers,partials,data) {
    var lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "					<a href=\"#\">"
    + container.escapeExpression((lookupProperty(helpers,"tNN")||(depth0 && lookupProperty(depth0,"tNN"))||container.hooks.helperMissing).call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? lookupProperty(depth0,"id") : depth0),{"name":"tNN","hash":{},"data":data,"loc":{"start":{"line":55,"column":17},"end":{"line":55,"column":27}}}))
    + "</a>\n";
},"compiler":[8,">= 4.3.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "<div id=\"new-note-fixed\">\n	<div>\n		<button type=\"button\" id=\"new-note\" class=\"icon-button-add\">"
    + alias4(((helper = (helper = lookupProperty(helpers,"newNoteTxt") || (depth0 != null ? lookupProperty(depth0,"newNoteTxt") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"newNoteTxt","hash":{},"data":data,"loc":{"start":{"line":3,"column":62},"end":{"line":3,"column":76}}}) : helper)))
    + "</button>\n	</div>\n</div>\n<li id=\"all-notes\">\n	<a href=\"#\" class=\"icon-home svg\">\n		"
    + alias4(((helper = (helper = lookupProperty(helpers,"allNotesTxt") || (depth0 != null ? lookupProperty(depth0,"allNotesTxt") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"allNotesTxt","hash":{},"data":data,"loc":{"start":{"line":8,"column":2},"end":{"line":8,"column":17}}}) : helper)))
    + "\n	</a>\n</li>\n<li id=\"shared-folder\" class=\"collapsible open\">\n	<button class=\"collapse\"></button>\n	<a href=\"#\" class=\"icon-share svg\">"
    + alias4((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Shared",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":13,"column":36},"end":{"line":13,"column":64}}}))
    + "</a>\n	<ul>\n		<li id=\"shared-by-you\">\n			<a href=\"#\" class=\"icon-share svg\">"
    + alias4((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Shared with others",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":16,"column":38},"end":{"line":16,"column":78}}}))
    + "</a>\n		</li>\n		<li id=\"shared-with-you\">\n			<a href=\"#\" class=\"icon-share svg\">"
    + alias4((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Shared with you",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":19,"column":38},"end":{"line":19,"column":75}}}))
    + "</a>\n		</li>\n	</ul>\n</li>\n<li id=\"colors-folder\" class=\"collapsible open\">\n	<button class=\"collapse\"></button>\n	<a href=\"#\" class=\"icon-search svg\">"
    + alias4(((helper = (helper = lookupProperty(helpers,"colorsTxt") || (depth0 != null ? lookupProperty(depth0,"colorsTxt") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"colorsTxt","hash":{},"data":data,"loc":{"start":{"line":25,"column":37},"end":{"line":25,"column":50}}}) : helper)))
    + "</a>\n	<ul>\n		<li class=\"color-filter\">\n			<button class=\"circle-toolbar icon-filter-checkmark any-color-filter\"></button>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"colors") : depth0),{"name":"each","hash":{},"fn":container.program(1, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":29,"column":3},"end":{"line":31,"column":12}}})) != null ? stack1 : "")
    + "		</li>\n	</ul>\n</li>\n<li id=\"tags-folder\" class=\"collapsible open\">\n	<button class=\"collapse\"></button>\n	<a href=\"#\" class=\"icon-tag svg\">"
    + alias4(((helper = (helper = lookupProperty(helpers,"tagsTxt") || (depth0 != null ? lookupProperty(depth0,"tagsTxt") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"tagsTxt","hash":{},"data":data,"loc":{"start":{"line":37,"column":34},"end":{"line":37,"column":45}}}) : helper)))
    + "</a>\n	<ul>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"tags") : depth0),{"name":"each","hash":{},"fn":container.program(3, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":39,"column":2},"end":{"line":43,"column":11}}})) != null ? stack1 : "")
    + "	</ul>\n</li>\n<li id=\"notes-folder\" class=\"collapsible open\">\n	<button class=\"collapse\"></button>\n	<a href=\"#\" class=\"icon-quicknotes svg\">"
    + alias4(((helper = (helper = lookupProperty(helpers,"notesTxt") || (depth0 != null ? lookupProperty(depth0,"notesTxt") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"notesTxt","hash":{},"data":data,"loc":{"start":{"line":48,"column":41},"end":{"line":48,"column":53}}}) : helper)))
    + "</a>\n	<ul>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"notes") : depth0),{"name":"each","hash":{},"fn":container.program(6, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":50,"column":2},"end":{"line":58,"column":11}}})) != null ? stack1 : "")
    + "	</ul>\n</li>\n";
},"useData":true});
templates['note-item'] = template({"1":function(container,depth0,helpers,partials,data) {
    return "shared";
},"3":function(container,depth0,helpers,partials,data) {
    return "shareowner";
},"5":function(container,depth0,helpers,partials,data) {
    return "1";
},"7":function(container,depth0,helpers,partials,data) {
    return "0";
},"9":function(container,depth0,helpers,partials,data) {
    var helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "					<div class='note-attach-grid'>\n						<div class=\"attach-preview note-attach\" attach-file-id=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"file_id") || (depth0 != null ? lookupProperty(depth0,"file_id") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"file_id","hash":{},"data":data,"loc":{"start":{"line":7,"column":62},"end":{"line":7,"column":73}}}) : helper)))
    + "\" data-background-image=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"preview_url") || (depth0 != null ? lookupProperty(depth0,"preview_url") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"preview_url","hash":{},"data":data,"loc":{"start":{"line":7,"column":98},"end":{"line":7,"column":113}}}) : helper)))
    + "\"></div>\n					</div>\n";
},"11":function(container,depth0,helpers,partials,data) {
    var lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "				<div class=\"icon-header-note icon-pinned fixed-header-icon\" title=\""
    + container.escapeExpression((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||container.hooks.helperMissing).call(depth0 != null ? depth0 : (container.nullContext || {}),"quicknotes","Unpin note",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":14,"column":71},"end":{"line":14,"column":102}}}))
    + "\"></div>\n";
},"13":function(container,depth0,helpers,partials,data) {
    var lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "				<div class=\"icon-header-note icon-pin hide-header-icon\" title=\""
    + container.escapeExpression((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||container.hooks.helperMissing).call(depth0 != null ? depth0 : (container.nullContext || {}),"quicknotes","Pin note",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":16,"column":67},"end":{"line":16,"column":96}}}))
    + "\"></div>\n";
},"15":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "					<div class=\"slim-share\" share-id=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"shared_user") || (depth0 != null ? lookupProperty(depth0,"shared_user") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"shared_user","hash":{},"data":data,"loc":{"start":{"line":27,"column":39},"end":{"line":27,"column":56}}}) : helper)))
    + "\" title=\"Shared with "
    + alias4(((helper = (helper = lookupProperty(helpers,"display_name") || (depth0 != null ? lookupProperty(depth0,"display_name") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"display_name","hash":{},"data":data,"loc":{"start":{"line":27,"column":77},"end":{"line":27,"column":95}}}) : helper)))
    + "\">"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"display_name") || (depth0 != null ? lookupProperty(depth0,"display_name") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"display_name","hash":{},"data":data,"loc":{"start":{"line":27,"column":97},"end":{"line":27,"column":117}}}) : helper))) != null ? stack1 : "")
    + "</div>\n";
},"17":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "					<div class=\"slim-tag\" tag-id=\""
    + container.escapeExpression(((helper = (helper = lookupProperty(helpers,"id") || (depth0 != null ? lookupProperty(depth0,"id") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"id","hash":{},"data":data,"loc":{"start":{"line":32,"column":35},"end":{"line":32,"column":43}}}) : helper)))
    + "\">"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"name") || (depth0 != null ? lookupProperty(depth0,"name") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"name","hash":{},"data":data,"loc":{"start":{"line":32,"column":45},"end":{"line":32,"column":57}}}) : helper))) != null ? stack1 : "")
    + "</div>\n";
},"compiler":[8,">= 4.3.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "<div class=\"note-grid-item\">\n	<div class=\"quicknote noselect "
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,((stack1 = (depth0 != null ? lookupProperty(depth0,"sharedBy") : depth0)) != null ? lookupProperty(stack1,"length") : stack1),{"name":"if","hash":{},"fn":container.program(1, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":2,"column":32},"end":{"line":2,"column":68}}})) != null ? stack1 : "")
    + " "
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,((stack1 = (depth0 != null ? lookupProperty(depth0,"sharedWith") : depth0)) != null ? lookupProperty(stack1,"length") : stack1),{"name":"if","hash":{},"fn":container.program(3, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":2,"column":69},"end":{"line":2,"column":111}}})) != null ? stack1 : "")
    + "\" style=\"background-color: "
    + alias4(((helper = (helper = lookupProperty(helpers,"color") || (depth0 != null ? lookupProperty(depth0,"color") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"color","hash":{},"data":data,"loc":{"start":{"line":2,"column":138},"end":{"line":2,"column":147}}}) : helper)))
    + "\" data-id=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"id") || (depth0 != null ? lookupProperty(depth0,"id") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"id","hash":{},"data":data,"loc":{"start":{"line":2,"column":158},"end":{"line":2,"column":166}}}) : helper)))
    + "\" data-pinned="
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,(depth0 != null ? lookupProperty(depth0,"isPinned") : depth0),{"name":"if","hash":{},"fn":container.program(5, data, 0),"inverse":container.program(7, data, 0),"data":data,"loc":{"start":{"line":2,"column":180},"end":{"line":2,"column":213}}})) != null ? stack1 : "")
    + " data-timestamp=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"timestamp") || (depth0 != null ? lookupProperty(depth0,"timestamp") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"timestamp","hash":{},"data":data,"loc":{"start":{"line":2,"column":230},"end":{"line":2,"column":245}}}) : helper)))
    + "\" >\n		<div class='note-header'>\n			<div class='note-attachts'>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"attachments") : depth0),{"name":"each","hash":{},"fn":container.program(9, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":5,"column":4},"end":{"line":9,"column":13}}})) != null ? stack1 : "")
    + "			</div>\n		</div>\n		<div class='note-body'>\n"
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,(depth0 != null ? lookupProperty(depth0,"isPinned") : depth0),{"name":"if","hash":{},"fn":container.program(11, data, 0),"inverse":container.program(13, data, 0),"data":data,"loc":{"start":{"line":13,"column":3},"end":{"line":17,"column":10}}})) != null ? stack1 : "")
    + "			<div class=\"icon-header-note hide-header-icon icon-delete-note\" title=\""
    + alias4((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Delete note",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":18,"column":74},"end":{"line":18,"column":106}}}))
    + "\"></div>\n			<div class='note-title'>\n				"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"title") || (depth0 != null ? lookupProperty(depth0,"title") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"title","hash":{},"data":data,"loc":{"start":{"line":20,"column":4},"end":{"line":20,"column":17}}}) : helper))) != null ? stack1 : "")
    + "\n			</div>\n			<div class='note-content'>\n				"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"content") || (depth0 != null ? lookupProperty(depth0,"content") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"content","hash":{},"data":data,"loc":{"start":{"line":23,"column":4},"end":{"line":23,"column":19}}}) : helper))) != null ? stack1 : "")
    + "\n			</div>\n			<div class='note-shares'>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"sharedWith") : depth0),{"name":"each","hash":{},"fn":container.program(15, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":26,"column":4},"end":{"line":28,"column":13}}})) != null ? stack1 : "")
    + "			</div>\n			<div class='note-tags'>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"tags") : depth0),{"name":"each","hash":{},"fn":container.program(17, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":31,"column":4},"end":{"line":33,"column":13}}})) != null ? stack1 : "")
    + "			</div>\n		<div>\n	</div>\n</div>";
},"useData":true});
templates['notes'] = template({"1":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3=container.escapeExpression, alias4="function", lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "<div id=\"notes-grid-div\" class=\"notes-grid\">\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"notes") : depth0),{"name":"each","hash":{},"fn":container.program(2, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":3,"column":1},"end":{"line":48,"column":10}}})) != null ? stack1 : "")
    + "</div>\n<div id=\"modal-note-div\" class=\"hide-modal-note modal-note-background\">\n	<div class=\"modal-content\">\n		<div class=\"quicknote note-active\" style=\"background-color: #F7EB96\" data-id=\"-1\">\n			<div class='note-header'>\n				<div class='note-attachts'></div>\n			</div>\n			<div class='note-body'>\n				<div class=\"icon-header-note icon-pin fixed-header-icon\" title=\""
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Pin note",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":57,"column":68},"end":{"line":57,"column":97}}}))
    + "\"></div>\n				<div contenteditable=\"true\" id='title-editable' class='note-title' data-placeholder=\""
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Title",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":58,"column":89},"end":{"line":58,"column":115}}}))
    + "\"></div>\n				<div contenteditable=\"true\" id='content-editable' class='note-content'></div>\n				<div class='note-shares'></div>\n				<div class='note-tags'></div>\n			</div>\n			<div class=\"note-editable-options\">\n				<div class=\"colors-toolbar\">\n					<button id='color-button' class='round-tool-button'>\n						<div class=\"icon-toggle-background\" title=\""
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Colors",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":66,"column":49},"end":{"line":66,"column":76}}}))
    + "\"></div>\n					</button>\n					<button id='share-button' class='round-tool-button'>\n						<div class=\"icon-shared\" title=\""
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Share note",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":69,"column":38},"end":{"line":69,"column":69}}}))
    + "\"></div>\n					</button>\n					<button id='tag-button' class='round-tool-button'>\n						<div class=\"icon-tag\" title=\""
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Tags",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":72,"column":35},"end":{"line":72,"column":60}}}))
    + "\"></div>\n					</button>\n					<button id='attach-button' class='round-tool-button'>\n						<div class=\"icon-picture\" title=\""
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Attach file",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":75,"column":39},"end":{"line":75,"column":71}}}))
    + "\"></div>\n					</button>\n				</div>\n				<div class=\"buttons-toolbar\">\n					<button id='cancel-button'>\n						"
    + alias3(((helper = (helper = lookupProperty(helpers,"cancelTxt") || (depth0 != null ? lookupProperty(depth0,"cancelTxt") : depth0)) != null ? helper : alias2),(typeof helper === alias4 ? helper.call(alias1,{"name":"cancelTxt","hash":{},"data":data,"loc":{"start":{"line":80,"column":6},"end":{"line":80,"column":21}}}) : helper)))
    + "\n					</button>\n					<button id='save-button'>\n						"
    + alias3(((helper = (helper = lookupProperty(helpers,"saveTxt") || (depth0 != null ? lookupProperty(depth0,"saveTxt") : depth0)) != null ? helper : alias2),(typeof helper === alias4 ? helper.call(alias1,{"name":"saveTxt","hash":{},"data":data,"loc":{"start":{"line":83,"column":6},"end":{"line":83,"column":19}}}) : helper)))
    + "\n					</button>\n					<button id='close-button'>"
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Close",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":85,"column":31},"end":{"line":85,"column":57}}}))
    + "</button>\n				</div>\n				<div style=\"clear: both;\"></div>\n			</div>\n			<div class=\"note-noneditable-options\">\n				<div class=\"buttons-toolbar\">\n					<button id='close-button'>"
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Close",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":91,"column":31},"end":{"line":91,"column":57}}}))
    + "</button>\n				</div>\n				<div style=\"clear: both;\"></div>\n			</div>\n		</div>\n	</div>\n</div>\n";
},"2":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "	<div class=\"note-grid-item\">\n		<div class=\"quicknote noselect "
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,((stack1 = (depth0 != null ? lookupProperty(depth0,"sharedBy") : depth0)) != null ? lookupProperty(stack1,"length") : stack1),{"name":"if","hash":{},"fn":container.program(3, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":5,"column":33},"end":{"line":5,"column":69}}})) != null ? stack1 : "")
    + " "
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,((stack1 = (depth0 != null ? lookupProperty(depth0,"sharedWith") : depth0)) != null ? lookupProperty(stack1,"length") : stack1),{"name":"if","hash":{},"fn":container.program(5, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":5,"column":70},"end":{"line":5,"column":112}}})) != null ? stack1 : "")
    + "\" style=\"background-color: "
    + alias4(((helper = (helper = lookupProperty(helpers,"color") || (depth0 != null ? lookupProperty(depth0,"color") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"color","hash":{},"data":data,"loc":{"start":{"line":5,"column":139},"end":{"line":5,"column":148}}}) : helper)))
    + "\" data-id=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"id") || (depth0 != null ? lookupProperty(depth0,"id") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"id","hash":{},"data":data,"loc":{"start":{"line":5,"column":159},"end":{"line":5,"column":167}}}) : helper)))
    + "\" data-pinned="
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,(depth0 != null ? lookupProperty(depth0,"isPinned") : depth0),{"name":"if","hash":{},"fn":container.program(7, data, 0),"inverse":container.program(9, data, 0),"data":data,"loc":{"start":{"line":5,"column":181},"end":{"line":5,"column":214}}})) != null ? stack1 : "")
    + " data-timestamp=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"timestamp") || (depth0 != null ? lookupProperty(depth0,"timestamp") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"timestamp","hash":{},"data":data,"loc":{"start":{"line":5,"column":231},"end":{"line":5,"column":246}}}) : helper)))
    + "\" >\n			<div class='note-header'>\n				<div class='note-attachts'>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"attachments") : depth0),{"name":"each","hash":{},"fn":container.program(11, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":8,"column":5},"end":{"line":12,"column":14}}})) != null ? stack1 : "")
    + "				</div>\n			</div>\n			<div class='note-body'>\n"
    + ((stack1 = lookupProperty(helpers,"if").call(alias1,(depth0 != null ? lookupProperty(depth0,"sharedBy") : depth0),{"name":"if","hash":{},"fn":container.program(13, data, 0),"inverse":container.program(15, data, 0),"data":data,"loc":{"start":{"line":16,"column":4},"end":{"line":26,"column":11}}})) != null ? stack1 : "")
    + "				<div class='note-title'>\n					"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"title") || (depth0 != null ? lookupProperty(depth0,"title") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"title","hash":{},"data":data,"loc":{"start":{"line":28,"column":5},"end":{"line":28,"column":18}}}) : helper))) != null ? stack1 : "")
    + "\n				</div>\n				<div class='note-content'>\n					"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"content") || (depth0 != null ? lookupProperty(depth0,"content") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"content","hash":{},"data":data,"loc":{"start":{"line":31,"column":5},"end":{"line":31,"column":20}}}) : helper))) != null ? stack1 : "")
    + "\n				</div>\n				<div class='note-shares'>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"sharedWith") : depth0),{"name":"each","hash":{},"fn":container.program(20, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":34,"column":5},"end":{"line":36,"column":14}}})) != null ? stack1 : "")
    + "				</div>\n				<div class='note-tags'>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(alias1,(depth0 != null ? lookupProperty(depth0,"tags") : depth0),{"name":"each","hash":{},"fn":container.program(22, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":39,"column":5},"end":{"line":43,"column":14}}})) != null ? stack1 : "")
    + "				</div>\n			</div>\n		</div>\n	</div>\n";
},"3":function(container,depth0,helpers,partials,data) {
    return "shared";
},"5":function(container,depth0,helpers,partials,data) {
    return "shareowner";
},"7":function(container,depth0,helpers,partials,data) {
    return "1";
},"9":function(container,depth0,helpers,partials,data) {
    return "0";
},"11":function(container,depth0,helpers,partials,data) {
    var helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "						<div class='note-attach-grid'>\n							<div class=\"attach-preview note-attach\" attach-file-id=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"file_id") || (depth0 != null ? lookupProperty(depth0,"file_id") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"file_id","hash":{},"data":data,"loc":{"start":{"line":10,"column":63},"end":{"line":10,"column":74}}}) : helper)))
    + "\" data-background-image=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"preview_url") || (depth0 != null ? lookupProperty(depth0,"preview_url") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"preview_url","hash":{},"data":data,"loc":{"start":{"line":10,"column":99},"end":{"line":10,"column":114}}}) : helper)))
    + "\"></div>\n						</div>\n";
},"13":function(container,depth0,helpers,partials,data) {
    var stack1, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "					<div class=\"icon-header-note icon-share\" title=\""
    + alias3((lookupProperty(helpers,"tSB")||(depth0 && lookupProperty(depth0,"tSB"))||alias2).call(alias1,((stack1 = ((stack1 = (depth0 != null ? lookupProperty(depth0,"sharedBy") : depth0)) != null ? lookupProperty(stack1,"0") : stack1)) != null ? lookupProperty(stack1,"display_name") : stack1),{"name":"tSB","hash":{},"data":data,"loc":{"start":{"line":17,"column":53},"end":{"line":17,"column":84}}}))
    + "\"></div>\n					<div class=\"icon-header-note hide-header-icon icon-delete-note\" title=\""
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Leave this shared note",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":18,"column":76},"end":{"line":18,"column":119}}}))
    + "\"></div>\n";
},"15":function(container,depth0,helpers,partials,data) {
    var stack1, alias1=depth0 != null ? depth0 : (container.nullContext || {}), lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return ((stack1 = lookupProperty(helpers,"if").call(alias1,(depth0 != null ? lookupProperty(depth0,"isPinned") : depth0),{"name":"if","hash":{},"fn":container.program(16, data, 0),"inverse":container.program(18, data, 0),"data":data,"loc":{"start":{"line":20,"column":5},"end":{"line":24,"column":12}}})) != null ? stack1 : "")
    + "					<div class=\"icon-header-note hide-header-icon icon-delete-note\" title=\""
    + container.escapeExpression((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||container.hooks.helperMissing).call(alias1,"quicknotes","Delete note",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":25,"column":76},"end":{"line":25,"column":108}}}))
    + "\"></div>\n";
},"16":function(container,depth0,helpers,partials,data) {
    var lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "						<div class=\"icon-header-note icon-pinned fixed-header-icon\" title=\""
    + container.escapeExpression((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||container.hooks.helperMissing).call(depth0 != null ? depth0 : (container.nullContext || {}),"quicknotes","Unpin note",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":21,"column":73},"end":{"line":21,"column":104}}}))
    + "\"></div>\n";
},"18":function(container,depth0,helpers,partials,data) {
    var lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "						<div class=\"icon-header-note icon-pin hide-header-icon\" title=\""
    + container.escapeExpression((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||container.hooks.helperMissing).call(depth0 != null ? depth0 : (container.nullContext || {}),"quicknotes","Pin note",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":23,"column":69},"end":{"line":23,"column":98}}}))
    + "\"></div>\n";
},"20":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "						<div class=\"slim-share\" share-id=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"shared_user") || (depth0 != null ? lookupProperty(depth0,"shared_user") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"shared_user","hash":{},"data":data,"loc":{"start":{"line":35,"column":40},"end":{"line":35,"column":57}}}) : helper)))
    + "\" title=\""
    + alias4((lookupProperty(helpers,"tSW")||(depth0 && lookupProperty(depth0,"tSW"))||alias2).call(alias1,(depth0 != null ? lookupProperty(depth0,"display_name") : depth0),{"name":"tSW","hash":{},"data":data,"loc":{"start":{"line":35,"column":66},"end":{"line":35,"column":86}}}))
    + "\">"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"display_name") || (depth0 != null ? lookupProperty(depth0,"display_name") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"display_name","hash":{},"data":data,"loc":{"start":{"line":35,"column":88},"end":{"line":35,"column":108}}}) : helper))) != null ? stack1 : "")
    + "</div>\n";
},"22":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "						<div class='slim-tag' tag-id=\""
    + container.escapeExpression(((helper = (helper = lookupProperty(helpers,"id") || (depth0 != null ? lookupProperty(depth0,"id") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"id","hash":{},"data":data,"loc":{"start":{"line":40,"column":36},"end":{"line":40,"column":44}}}) : helper)))
    + "\">\n							"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"name") || (depth0 != null ? lookupProperty(depth0,"name") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"name","hash":{},"data":data,"loc":{"start":{"line":41,"column":7},"end":{"line":41,"column":19}}}) : helper))) != null ? stack1 : "")
    + "\n						</div>\n";
},"24":function(container,depth0,helpers,partials,data) {
    var stack1, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return ((stack1 = lookupProperty(helpers,"if").call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? lookupProperty(depth0,"loaded") : depth0),{"name":"if","hash":{},"fn":container.program(25, data, 0),"inverse":container.program(27, data, 0),"data":data,"loc":{"start":{"line":98,"column":0},"end":{"line":113,"column":0}}})) != null ? stack1 : "");
},"25":function(container,depth0,helpers,partials,data) {
    var helper, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "<div class=\"emptycontent\">\n	<div class=\"icon-quicknotes svg\"></div>\n	<h2>\n		"
    + container.escapeExpression(((helper = (helper = lookupProperty(helpers,"emptyMsg") || (depth0 != null ? lookupProperty(depth0,"emptyMsg") : depth0)) != null ? helper : container.hooks.helperMissing),(typeof helper === "function" ? helper.call(depth0 != null ? depth0 : (container.nullContext || {}),{"name":"emptyMsg","hash":{},"data":data,"loc":{"start":{"line":102,"column":2},"end":{"line":102,"column":16}}}) : helper)))
    + "\n	</h2>\n</div>\n";
},"27":function(container,depth0,helpers,partials,data) {
    var helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "<div class=\"emptycontent\">\n	<div class=\"icon-quicknotes svg\"></div>\n	<h2>\n		"
    + alias4(((helper = (helper = lookupProperty(helpers,"loadingMsg") || (depth0 != null ? lookupProperty(depth0,"loadingMsg") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"loadingMsg","hash":{},"data":data,"loc":{"start":{"line":109,"column":2},"end":{"line":109,"column":16}}}) : helper)))
    + "\n	</h2>\n	<img class=\"loadingimport\" src=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"loadingIcon") || (depth0 != null ? lookupProperty(depth0,"loadingIcon") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"loadingIcon","hash":{},"data":data,"loc":{"start":{"line":111,"column":33},"end":{"line":111,"column":48}}}) : helper)))
    + "\" />\n</div>\n";
},"compiler":[8,">= 4.3.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return ((stack1 = lookupProperty(helpers,"if").call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? lookupProperty(depth0,"notes") : depth0),{"name":"if","hash":{},"fn":container.program(1, data, 0),"inverse":container.program(24, data, 0),"data":data,"loc":{"start":{"line":1,"column":0},"end":{"line":113,"column":7}}})) != null ? stack1 : "");
},"useData":true});
templates['settings'] = template({"compiler":[8,">= 4.3.0"],"main":function(container,depth0,helpers,partials,data) {
    var alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "<p class=\"app-settings\">\n	<input id=\"explicit-save-notes\" type=\"checkbox\" class=\"checkbox\">\n	<label for=\"explicit-save-notes\">"
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","When editing notes, show Save and Cancel buttons to save them explicitly",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":3,"column":34},"end":{"line":3,"column":127}}}))
    + "</label>\n</p>\n<div>\n	<label for=\"sort-select\">"
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Sort by:",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":6,"column":26},"end":{"line":6,"column":55}}}))
    + "</label>\n	<select name=\"sort-select\" id=\"sort-select\">\n		<option value=\"title\">"
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Title",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":8,"column":24},"end":{"line":8,"column":50}}}))
    + "</option>\n		<option value=\"created\">"
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Newest first",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":9,"column":26},"end":{"line":9,"column":59}}}))
    + "</option>\n		<option value=\"updated\">"
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Updated first",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":10,"column":26},"end":{"line":10,"column":60}}}))
    + "</option>\n	</select>\n</div>\n<div>\n	<label>"
    + alias3((lookupProperty(helpers,"t")||(depth0 && lookupProperty(depth0,"t"))||alias2).call(alias1,"quicknotes","Default color for new notes",{"name":"t","hash":{},"data":data,"loc":{"start":{"line":14,"column":8},"end":{"line":14,"column":56}}}))
    + "</label>\n</div>\n<div id=\"setting-defaul-color\">\n	<div id=\"defaultColor\" style=\"display: flex; justify-content: center;\">\n		<div class=\"colors-toolbar\">\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #F7EB96\"></a>\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #88B7E3\"></a>\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #C1ECB0\"></a>\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #BFA6E9\"></a>\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #DAF188\"></a>\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #FF96AC\"></a>\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #FCF66F\"></a>\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #F2F1EF\"></a>\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #C1D756\"></a>\n			<a href=\"#\" class=\"circle-toolbar\" style=\"background-color: #CECECE\"></a>\n		</div>\n	</div>\n</div>";
},"useData":true});
templates['shares'] = template({"1":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", alias4=container.escapeExpression, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "		<div class=\"slim-share\" share-id=\""
    + alias4(((helper = (helper = lookupProperty(helpers,"shared_user") || (depth0 != null ? lookupProperty(depth0,"shared_user") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"shared_user","hash":{},"data":data,"loc":{"start":{"line":3,"column":36},"end":{"line":3,"column":53}}}) : helper)))
    + "\" title=\""
    + alias4((lookupProperty(helpers,"tSW")||(depth0 && lookupProperty(depth0,"tSW"))||alias2).call(alias1,(depth0 != null ? lookupProperty(depth0,"display_name") : depth0),{"name":"tSW","hash":{},"data":data,"loc":{"start":{"line":3,"column":62},"end":{"line":3,"column":82}}}))
    + "\">"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"display_name") || (depth0 != null ? lookupProperty(depth0,"display_name") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"display_name","hash":{},"data":data,"loc":{"start":{"line":3,"column":84},"end":{"line":3,"column":104}}}) : helper))) != null ? stack1 : "")
    + "</div>\n";
},"compiler":[8,">= 4.3.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "<div class='note-shares'>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? lookupProperty(depth0,"sharedWith") : depth0),{"name":"each","hash":{},"fn":container.program(1, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":2,"column":1},"end":{"line":4,"column":10}}})) != null ? stack1 : "")
    + "</div>";
},"useData":true});
templates['tags'] = template({"1":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.hooks.helperMissing, alias3="function", lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "		<div class=\"slim-tag\" tag-id=\""
    + container.escapeExpression(((helper = (helper = lookupProperty(helpers,"id") || (depth0 != null ? lookupProperty(depth0,"id") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"id","hash":{},"data":data,"loc":{"start":{"line":3,"column":32},"end":{"line":3,"column":40}}}) : helper)))
    + "\">"
    + ((stack1 = ((helper = (helper = lookupProperty(helpers,"name") || (depth0 != null ? lookupProperty(depth0,"name") : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"name","hash":{},"data":data,"loc":{"start":{"line":3,"column":42},"end":{"line":3,"column":54}}}) : helper))) != null ? stack1 : "")
    + "</div>\n";
},"compiler":[8,">= 4.3.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, lookupProperty = container.lookupProperty || function(parent, propertyName) {
        if (Object.prototype.hasOwnProperty.call(parent, propertyName)) {
          return parent[propertyName];
        }
        return undefined
    };

  return "<div class='note-tags'>\n"
    + ((stack1 = lookupProperty(helpers,"each").call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? lookupProperty(depth0,"tags") : depth0),{"name":"each","hash":{},"fn":container.program(1, data, 0),"inverse":container.noop,"data":data,"loc":{"start":{"line":2,"column":1},"end":{"line":4,"column":10}}})) != null ? stack1 : "")
    + "</div>";
},"useData":true});
})();