import GlobalModel from '../../model/Global';
import { Dictionary } from '../../util/types';
import { DataZoomPayloadBatchItem } from './helper';
export declare type DataZoomStoreSnapshot = Dictionary<DataZoomPayloadBatchItem>;
/**
 * @param ecModel
 * @param newSnapshot key is dataZoomId
 */
export declare function push(ecModel: GlobalModel, newSnapshot: DataZoomStoreSnapshot): void;
export declare function pop(ecModel: GlobalModel): Dictionary<DataZoomPayloadBatchItem>;
export declare function clear(ecModel: GlobalModel): void;
export declare function count(ecModel: GlobalModel): number;
