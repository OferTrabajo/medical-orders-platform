export class ValidateItemDto {
  type!: string;
  name!: string;
  price!: number;
}

export class ValidateItemsDto {
  items!: ValidateItemDto[];
}
