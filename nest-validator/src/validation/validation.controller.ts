import { Body, Controller, Post, UseGuards } from '@nestjs/common';
import { ValidateItemsDto } from './dto/validate-items.dto';
import { ValidationService } from './validation.service';
import { ServiceJwtGuard } from '../common/guards/service-jwt.guard';

@Controller()
export class ValidationController {
  constructor(private readonly validationService: ValidationService) {}

  @UseGuards(ServiceJwtGuard)
  @Post('validate-items')
  validateItems(@Body() payload: ValidateItemsDto) {
    return this.validationService.validateItems(payload);
  }
}
