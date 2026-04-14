import { Injectable } from '@nestjs/common';
import { ValidateItemsDto } from './dto/validate-items.dto';

@Injectable()
export class ValidationService {
  validateItems(payload: ValidateItemsDto) {
    const errors: { item_name: string; reason: string }[] = [];

    for (const item of payload.items) {
      if (item.type === 'service') {
        continue;
      }

      if (item.type === 'medication' && item.price > 20000) {
        errors.push({
          item_name: item.name,
          reason: 'Medication price cannot exceed 20000',
        });
      }
    }

    return {
      valid: errors.length === 0,
      errors,
    };
  }
}
