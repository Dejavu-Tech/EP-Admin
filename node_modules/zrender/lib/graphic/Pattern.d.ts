import { ImageLike } from '../core/types';
declare type ImagePatternRepeat = 'repeat' | 'repeat-x' | 'repeat-y' | 'no-repeat';
export interface PatternObjectBase {
    id?: number;
    type?: 'pattern';
    x?: number;
    y?: number;
    rotation?: number;
    scaleX?: number;
    scaleY?: number;
}
export interface ImagePatternObject extends PatternObjectBase {
    image: ImageLike | string;
    repeat?: ImagePatternRepeat;
}
export interface InnerImagePatternObject extends ImagePatternObject {
    __image?: ImageLike;
}
export interface SVGPatternObject extends PatternObjectBase {
    svgElement?: SVGElement;
    svgWidth?: number;
    svgHeight?: number;
}
export declare type PatternObject = ImagePatternObject | SVGPatternObject;
declare class Pattern {
    type: 'pattern';
    image: ImageLike | string;
    svgElement: SVGElement;
    repeat: ImagePatternRepeat;
    x: number;
    y: number;
    rotation: number;
    scaleX: number;
    scaleY: number;
    constructor(image: ImageLike | string, repeat: ImagePatternRepeat);
}
export default Pattern;
