import{aY as b,o as d,c as y,b as u,a_ as o,a$ as h,_ as m,r as S,g as v,Q as w}from"../../assets/app-8df99815.js";var g=`
@layer primevue {
    .p-inputswitch {
        display: inline-block;
    }

    .p-inputswitch-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border: 1px solid transparent;
    }

    .p-inputswitch-slider:before {
        position: absolute;
        content: '';
        top: 50%;
    }
}
`,O={root:{position:"relative"}},k={root:function(t){var n=t.instance,r=t.props;return["p-inputswitch p-component",{"p-inputswitch-checked":n.checked,"p-disabled":r.disabled,"p-focus":n.focused}]},slider:"p-inputswitch-slider"},P=b.extend({name:"inputswitch",css:g,classes:k,inlineStyles:O}),j={name:"BaseInputSwitch",extends:h,props:{modelValue:{type:null,default:!1},trueValue:{type:null,default:!0},falseValue:{type:null,default:!1},disabled:{type:Boolean,default:!1},inputId:{type:String,default:null},inputClass:{type:[String,Object],default:null},inputStyle:{type:Object,default:null},inputProps:{type:null,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null}},style:P,provide:function(){return{$parentInstance:this}}},f={name:"InputSwitch",extends:j,emits:["click","update:modelValue","change","input","focus","blur"],data:function(){return{focused:!1}},methods:{onClick:function(t){if(!this.disabled){var n=this.checked?this.falseValue:this.trueValue;this.$emit("click",t),this.$emit("update:modelValue",n),this.$emit("change",t),this.$emit("input",n),this.$refs.input.focus()}},onFocus:function(t){this.focused=!0,this.$emit("focus",t)},onBlur:function(t){this.focused=!1,this.$emit("blur",t)}},computed:{checked:function(){return this.modelValue===this.trueValue}}};function l(e){"@babel/helpers - typeof";return l=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(t){return typeof t}:function(t){return t&&typeof Symbol=="function"&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},l(e)}function c(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(a){return Object.getOwnPropertyDescriptor(e,a).enumerable})),n.push.apply(n,r)}return n}function p(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]!=null?arguments[t]:{};t%2?c(Object(n),!0).forEach(function(r){$(e,r,n[r])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):c(Object(n)).forEach(function(r){Object.defineProperty(e,r,Object.getOwnPropertyDescriptor(n,r))})}return e}function $(e,t,n){return t=B(t),t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function B(e){var t=I(e,"string");return l(t)=="symbol"?t:String(t)}function I(e,t){if(l(e)!="object"||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t||"default");if(l(r)!="object")return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return(t==="string"?String:Number)(e)}var V=["id","checked","disabled","aria-checked","aria-labelledby","aria-label"];function C(e,t,n,r,a,s){return d(),y("div",o({class:e.cx("root"),style:e.sx("root"),onClick:t[2]||(t[2]=function(i){return s.onClick(i)})},e.ptm("root"),{"data-pc-name":"inputswitch"}),[u("div",o({class:"p-hidden-accessible"},e.ptm("hiddenInputWrapper"),{"data-p-hidden-accessible":!0}),[u("input",o({ref:"input",id:e.inputId,type:"checkbox",role:"switch",class:e.inputClass,style:e.inputStyle,checked:s.checked,disabled:e.disabled,"aria-checked":s.checked,"aria-labelledby":e.ariaLabelledby,"aria-label":e.ariaLabel,onFocus:t[0]||(t[0]=function(i){return s.onFocus(i)}),onBlur:t[1]||(t[1]=function(i){return s.onBlur(i)})},p(p({},e.inputProps),e.ptm("hiddenInput"))),null,16,V)],16),u("span",o({class:e.cx("slider")},e.ptm("slider")),null,16)],16)}f.render=C;const D={props:{size:{type:String,default:"sm"}},components:{InputSwitch:f}};function E(e,t,n,r,a,s){const i=S("InputSwitch");return d(),v(i,{class:w(`base-switch-${n.size}`)},null,8,["class"])}const L=m(D,[["render",E]]);export{L as B};
