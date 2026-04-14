import { Body, Controller, Post } from '@nestjs/common';
import { ValidateItemsDto } from './dto/validate-items.dto';
import { ValidationService } from './validation.service';

@Controller()
export class ValidationController {
  constructor(private readonly validationService: ValidationService) {}

  @Post('validate-items')
  validateItems(@Body() payload: ValidateItemsDto) {
    return this.validationService.validateItems(payload);
  }
}
